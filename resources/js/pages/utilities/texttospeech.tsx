import InputError from '@/components/input-error';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { PlaceholderPattern } from '@/components/ui/placeholder-pattern';
import { Textarea } from '@/components/ui/textarea';
import AppLayout from '@/layouts/app-layout';
import { type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/react';
import { Download, Loader2 } from 'lucide-react'; // Importa íconos de Lucide
import { FormEventHandler, useState } from 'react';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Text to Speech',
        href: '/text-to-speech',
    },
];

export default function Dashboard() {
    const [isLoading, setIsLoading] = useState(false);
    const [statusMessage, setStatusMessage] = useState<string | null>(null); // Mensaje de éxito o error
    const [errors, setErrors] = useState({
        audioToAudio: '',
        audioToText: '',
        textToAudio: '',
    });

    const [results, setResults] = useState({
        audioToAudio: null as string | null,
        audioToText: null as string | null,
        textToAudio: null as string | null,
    });

    const sendRequest = async (url: string, formData: FormData, type: keyof typeof results) => {
        try {
            setIsLoading(true);
            setStatusMessage(null); // Limpia el mensaje de estado
            setErrors((prev) => ({ ...prev, [type]: '' }));
            setResults({ audioToAudio: null, audioToText: null, textToAudio: null });

            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

            const response = await fetch(url, {
                method: 'POST',
                body: formData,
                headers: {
                    'X-CSRF-TOKEN': csrfToken || '',
                },
            });

            if (!response.ok) {
                const errorData = await response.json();
                throw new Error(errorData.message || 'An error occurred');
            }

            const data = await response.json();

            // Manejo de respuesta según el tipo
            if (type === 'textToAudio') {
                setResults((prev) => ({ ...prev, [type]: data.data.audio_url }));
            } else if (type === 'audioToText') {
                setResults((prev) => ({ ...prev, [type]: data.data.text }));
            } else if (type === 'audioToAudio') {
                setResults((prev) => ({ ...prev, [type]: data.data.audio_url }));
            }

            setStatusMessage('Operation completed successfully!');
        } catch (error: any) {
            setErrors((prev) => ({ ...prev, [type]: error.message }));
            setStatusMessage('An error occurred during the operation.');
        } finally {
            setIsLoading(false);
        }
    };

    const handleAudioToAudioSubmit: FormEventHandler = async (e) => {
        e.preventDefault();
        const formData = new FormData();
        const fileInput = (e.target as HTMLFormElement).elements.namedItem('audio-to-audio') as HTMLInputElement;
        if (fileInput?.files?.[0]) {
            formData.append('audio', fileInput.files[0]);
            await sendRequest(route('texttospeech.audioToAudio'), formData, 'audioToAudio');
        }
    };

    const handleAudioToTextSubmit: FormEventHandler = async (e) => {
        e.preventDefault();
        const formData = new FormData();
        const fileInput = (e.target as HTMLFormElement).elements.namedItem('audio-to-text') as HTMLInputElement;
        if (fileInput?.files?.[0]) {
            formData.append('audio', fileInput.files[0]);
            await sendRequest(route('texttospeech.audioToText'), formData, 'audioToText');
        }
    };

    const handleTextToAudioSubmit: FormEventHandler = async (e) => {
        e.preventDefault();
        const formData = new FormData();
        const textInput = (e.target as HTMLFormElement).elements.namedItem('text-to-audio') as HTMLInputElement;
        if (textInput?.value) {
            formData.append('text', textInput.value);
            await sendRequest(route('texttospeech.textToAudio'), formData, 'textToAudio');
        }
    };

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Text to Speech" />
            <div className="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
                <div className="grid auto-rows-min gap-4 md:grid-cols-3">
                    {/* Card 1: Audio to Audio */}
                    <div className="border-sidebar-border/70 dark:border-sidebar-border relative h-[200px] overflow-hidden rounded-xl border p-4">
                        <form onSubmit={handleAudioToAudioSubmit} className="space-y-4">
                            <div>
                                <Label htmlFor="audio-to-audio">Upload Audio</Label>
                                <Input id="audio-to-audio" name="audio-to-audio" type="file" accept="audio/*" className="mt-1 block w-full" />
                                {errors.audioToAudio && <InputError message={errors.audioToAudio} />}
                            </div>
                            <Button type="submit" disabled={isLoading}>
                                <Loader2 className={`mr-2 h-4 w-4 ${isLoading ? 'animate-spin' : ''}`} />
                                Process Audio
                            </Button>
                        </form>
                    </div>

                    {/* Card 2: Audio to Text */}
                    <div className="border-sidebar-border/70 dark:border-sidebar-border relative h-[200px] overflow-hidden rounded-xl border p-4">
                        <form onSubmit={handleAudioToTextSubmit} className="space-y-4">
                            <div>
                                <Label htmlFor="audio-to-text">Upload Audio</Label>
                                <Input id="audio-to-text" name="audio-to-text" type="file" accept="audio/*" className="mt-1 block w-full" />
                                {errors.audioToText && <InputError message={errors.audioToText} />}
                            </div>
                            <Button type="submit" disabled={isLoading}>
                                <Loader2 className={`mr-2 h-4 w-4 ${isLoading ? 'animate-spin' : ''}`} />
                                Generate Text
                            </Button>
                        </form>
                    </div>

                    {/* Card 3: Text to Audio */}
                    <div className="border-sidebar-border/70 dark:border-sidebar-border relative h-[200px] overflow-hidden rounded-xl border p-4">
                        <form onSubmit={handleTextToAudioSubmit} className="space-y-4">
                            <div>
                                <Label htmlFor="text-to-audio">Enter Text</Label>
                                <Input id="text-to-audio" name="text-to-audio" type="text" className="mt-1 block w-full" />
                                {errors.textToAudio && <InputError message={errors.textToAudio} />}
                            </div>
                            <Button type="submit" disabled={isLoading}>
                                <Loader2 className={`mr-2 h-4 w-4 ${isLoading ? 'animate-spin' : ''}`} />
                                Generate Audio
                            </Button>
                        </form>
                    </div>
                </div>
                {/* Placeholder para mostrar resultados o mensajes */}
                <div className="border-sidebar-border/70 dark:border-sidebar-border relative flex min-h-[200px] flex-1 flex-col items-center justify-center overflow-hidden rounded-xl border">
                    {isLoading ? (
                        <Loader2 className="h-8 w-8 animate-spin text-blue-500" />
                    ) : (
                        <>
                            {results.audioToAudio && (
                                <a href={results.audioToAudio} download className="flex items-center gap-2">
                                    <Button className="bg-blue-500 text-white hover:bg-blue-600">
                                        <Download className="mr-2 h-4 w-4" />
                                        Download Processed Audio
                                    </Button>
                                </a>
                            )}
                            {!results.audioToAudio && results.audioToText && !results.textToAudio && (
                                <Textarea readOnly value={results.audioToText} className="w-full rounded border p-2 text-gray-700" />
                            )}
                            {results.textToAudio && (
                                <a href={results.textToAudio} download className="flex items-center gap-2">
                                    <Button className="bg-blue-500 text-white hover:bg-blue-600">
                                        <Download className="mr-2 h-4 w-4" />
                                        Download Generated Audio
                                    </Button>
                                </a>
                            )}
                            {!results.audioToAudio && !results.audioToText && !results.textToAudio && (
                                <PlaceholderPattern className="absolute inset-0 size-full stroke-neutral-900/20 dark:stroke-neutral-100/20" />
                            )}
                        </>
                    )}
                </div>
            </div>
        </AppLayout>
    );
}
