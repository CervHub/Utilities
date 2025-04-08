<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class MakeRepository extends Command
{
    /**
     * El nombre y la firma del comando.
     *
     * @var string
     */
    protected $signature = 'make:repository {name}';

    /**
     * La descripción del comando.
     *
     * @var string
     */
    protected $description = 'Crea una nueva clase de repositorio';

    /**
     * Ejecuta el comando.
     *
     * @return int
     */
    public function handle()
    {
        // Obtener el nombre del repositorio
        $name = $this->argument('name');

        // Ruta donde se generará el archivo
        $path = app_path("Repositories/{$name}.php");

        // Comprobar si el archivo ya existe
        if (File::exists($path)) {
            $this->error("El repositorio {$name} ya existe.");
            return Command::FAILURE;
        }

        // Crear el directorio si no existe
        File::ensureDirectoryExists(dirname($path));

        // Crear el contenido del repositorio
        $stub = "<?php\n\nnamespace App\Repositories;\n\nclass {$name}\n{\n    protected \$model;\n\n    public function __construct(\$model)\n    {\n        \$this->model = \$model;\n    }\n\n    // Métodos comunes para el repositorio\n    public function all()\n    {\n        return \$this->model->all();\n    }\n\n    public function find(\$id)\n    {\n        return \$this->model->find(\$id);\n    }\n\n    public function create(array \$data)\n    {\n        return \$this->model->create(\$data);\n    }\n\n    public function update(\$id, array \$data)\n    {\n        \$record = \$this->model->find(\$id);\n        if (\$record) {\n            \$record->update(\$data);\n            return \$record;\n        }\n        return null;\n    }\n\n    public function delete(\$id)\n    {\n        \$record = \$this->model->find(\$id);\n        if (\$record) {\n            \$record->delete();\n            return true;\n        }\n        return false;\n    }\n}\n";

        // Guardar el archivo
        File::put($path, $stub);

        $this->info("El repositorio {$name} se ha creado exitosamente.");
        return Command::SUCCESS;
    }
}
