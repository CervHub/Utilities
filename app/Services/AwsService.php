<?php

namespace App\Services;

use Aws\S3\S3Client;
use Aws\TranscribeService\TranscribeServiceClient;
use Aws\Polly\PollyClient;
use App\Repositories\OpenAiRepository;
use Exception;
use Illuminate\Support\Facades\Log;

class AwsService
{
    protected $sdk;
    protected $openaiRepository;

    public function __construct()
    {
        $this->sdk = new \Aws\Sdk([
            'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
            'version' => 'latest',
            'credentials' => [
                'key'    => env('AWS_ACCESS_KEY_ID'),
                'secret' => env('AWS_SECRET_ACCESS_KEY'),
            ],
        ]);
        $this->openaiRepository = new OpenAiRepository();
    }

    public function s3()
    {
        return $this->sdk->createS3();
    }

    public function transcribe()
    {
        return $this->sdk->createTranscribeService();
    }

    public function polly()
    {
        return $this->sdk->createPolly();
    }

    /**
     * Función genérica para guardar archivos en S3
     */
    public function uploadToS3($file, $folder = 'uploads')
    {
        try {
            $bucketName = env('AWS_BUCKET');
            $fileName = uniqid() . '.' . $file->getClientOriginalExtension();
            $filePath = $file->getPathname();
            $s3Key = "{$folder}/{$fileName}";

            $s3Client = $this->s3();
            $s3Client->putObject([
                'Bucket' => $bucketName,
                'Key' => $s3Key,
                'SourceFile' => $filePath,
            ]);

            return [
                's3_key' => $s3Key,
                'url' => "s3://{$bucketName}/{$s3Key}",
            ];
        } catch (Exception $e) {
            Log::error("Error uploading file to S3: " . $e->getMessage());
            throw new Exception("Error uploading file to S3: " . $e->getMessage());
        }
    }

    /**
     * Convertir texto a audio usando Amazon Polly
     */
    public function convertTextToAudio($text)
    {
        try {
            $pollyClient = $this->polly();

            $result = $pollyClient->synthesizeSpeech([
                'OutputFormat' => 'mp3',
                'Text' => $text,
                'VoiceId' => 'Lucia',
                'LanguageCode' => 'es-ES',
            ]);

            $audioDirectory = public_path('audio');
            if (!file_exists($audioDirectory)) {
                mkdir($audioDirectory, 0755, true);
            }

            $audioFileName = uniqid() . '.mp3';
            $audioFilePath = "{$audioDirectory}/{$audioFileName}";
            file_put_contents($audioFilePath, $result['AudioStream']->getContents());

            return [
                'message' => 'Text converted to audio successfully',
                'audio_path' => $audioFilePath,
                'audio_url' => url("audio/{$audioFileName}"),
            ];
        } catch (Exception $e) {
            Log::error("Error converting text to audio: " . $e->getMessage());
            throw new Exception("Error converting text to audio: " . $e->getMessage());
        }
    }

    public function convertTextToAudioIA($text)
    {
        try {
            $pollyClient = $this->polly();

            // Paso 2: Procesar el texto usando OpenAI
            $processedText = $this->openaiRepository->chat($text);

            $result = $pollyClient->synthesizeSpeech([
                'OutputFormat' => 'mp3',
                'Text' => $processedText,
                'VoiceId' => 'Lucia',
                'LanguageCode' => 'es-ES',
            ]);

            $audioDirectory = public_path('audio');
            if (!file_exists($audioDirectory)) {
                mkdir($audioDirectory, 0755, true);
            }

            $audioFileName = uniqid() . '.mp3';
            $audioFilePath = "{$audioDirectory}/{$audioFileName}";
            file_put_contents($audioFilePath, $result['AudioStream']->getContents());

            return [
                'message' => 'Text converted to audio successfully',
                'audio_path' => $audioFilePath,
                'audio_url' => url("audio/{$audioFileName}"),
            ];
        } catch (Exception $e) {
            Log::error("Error converting text to audio: " . $e->getMessage());
            throw new Exception("Error converting text to audio: " . $e->getMessage());
        }
    }

    /**
     * Procesar el archivo de audio y devolver el texto transcrito
     */
    public function convertAudioToText($audioFile)
    {
        try {
            $bucketName = env('AWS_BUCKET');
            $fileName = uniqid() . '.' . $audioFile->getClientOriginalExtension();
            $filePath = $audioFile->getPathname();
            $s3Key = "transcriptions/{$fileName}";

            $s3Client = $this->s3();
            $s3Client->putObject([
                'Bucket' => $bucketName,
                'Key' => $s3Key,
                'SourceFile' => $filePath,
            ]);

            $mediaUri = "s3://{$bucketName}/{$s3Key}";

            $transcribeClient = $this->transcribe();
            $jobName = 'transcription_' . uniqid();
            $transcribeClient->startTranscriptionJob([
                'TranscriptionJobName' => $jobName,
                'LanguageCode' => 'es-ES',
                'Media' => [
                    'MediaFileUri' => $mediaUri,
                ],
                'MediaFormat' => $audioFile->getClientOriginalExtension(),
                'OutputBucketName' => $bucketName,
            ]);

            do {
                $result = $transcribeClient->getTranscriptionJob([
                    'TranscriptionJobName' => $jobName,
                ]);
                $status = $result['TranscriptionJob']['TranscriptionJobStatus'];
                sleep(5);
            } while ($status === 'IN_PROGRESS');

            if ($status === 'COMPLETED') {
                $transcriptionUri = $result['TranscriptionJob']['Transcript']['TranscriptFileUri'];

                $transcriptionJson = file_get_contents($transcriptionUri);
                $transcriptionData = json_decode($transcriptionJson, true);

                return [
                    'message' => 'Transcription completed successfully',
                    'text' => $transcriptionData['results']['transcripts'][0]['transcript'],
                ];
            } else {
                throw new Exception('Transcription job failed with status: ' . $status);
            }
        } catch (Exception $e) {
            Log::error("Error processing audio file: " . $e->getMessage());
            throw new Exception("Error processing audio file: " . $e->getMessage());
        }
    }
    /**
     * Procesar el archivo de audio, agregar "hola" al inicio y generar un nuevo archivo de audio.
     */
    public function processAudioFile($audioFile)
    {
        try {
            // Paso 1: Convertir el audio recibido a texto
            $transcriptionResult = $this->convertAudioToText($audioFile);
            $originalText = $transcriptionResult['text'];

            // Paso 2: Procesar el texto usando OpenAI
            $processedText = $this->openaiRepository->chat($originalText);

            // Paso 3: Convertir el texto procesado a audio usando Amazon Polly
            $pollyClient = $this->polly();
            $result = $pollyClient->synthesizeSpeech([
                'OutputFormat' => 'mp3',
                'Text' => $processedText,
                'VoiceId' => 'Lucia',
                'LanguageCode' => 'es-ES',
            ]);

            // Paso 4: Guardar el nuevo archivo de audio
            $audioDirectory = public_path('audio');
            if (!file_exists($audioDirectory)) {
                mkdir($audioDirectory, 0755, true);
            }

            $finalFileName = uniqid('final_') . '.mp3';
            $finalFilePath = "{$audioDirectory}/{$finalFileName}";
            file_put_contents($finalFilePath, $result['AudioStream']->getContents());

            return [
                'message' => 'Audio processed successfully',
                'audio_path' => $finalFilePath,
                'audio_url' => url("audio/{$finalFileName}"),
            ];
        } catch (Exception $e) {
            Log::error("Error processing audio file: " . $e->getMessage());
            throw new Exception("Error processing audio file: " . $e->getMessage());
        }
    }
}
