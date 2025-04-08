<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class MakeService extends Command
{
    /**
     * El nombre y la firma del comando.
     *
     * @var string
     */
    protected $signature = 'make:service {name}';

    /**
     * La descripción del comando.
     *
     * @var string
     */
    protected $description = 'Crea una nueva clase de servicio';

    /**
     * Ejecuta el comando.
     *
     * @return int
     */
    public function handle()
    {
        // Obtener el nombre del servicio
        $name = $this->argument('name');

        // Ruta donde se generará el archivo
        $path = app_path("Services/{$name}.php");

        // Comprobar si el archivo ya existe
        if (File::exists($path)) {
            $this->error("El servicio {$name} ya existe.");
            return Command::FAILURE;
        }

        // Crear el directorio si no existe
        File::ensureDirectoryExists(dirname($path));

        // Crear el contenido del servicio
        $stub = "<?php\n\nnamespace App\Services;\n\nclass {$name}\n{\n    // Tu lógica de servicio aquí\n}\n";

        // Guardar el archivo
        File::put($path, $stub);

        $this->info("El servicio {$name} se ha creado exitosamente.");
        return Command::SUCCESS;
    }
}
