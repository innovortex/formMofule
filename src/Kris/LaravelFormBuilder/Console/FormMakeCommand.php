<?php namespace Kris\LaravelFormBuilder\Console;

use Illuminate\Console\GeneratorCommand;
use Illuminate\Filesystem\Filesystem;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

class FormMakeCommand extends GeneratorCommand
{

    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'make:form';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a form builder class';

    /**
     * Type of the file generated
     */
    protected $type = 'Form';

    /**
     * @var FormGenerator
     */
    private $formGenerator;

    public function __construct(Filesystem $files, FormGenerator $formGenerator)
    {
        parent::__construct($files);
        $this->formGenerator = $formGenerator;
    }

    /**
     * Get the console command arguments.
     *
     * @return array
     */
    protected function getArguments()
    {
        return array(
            array('name', InputArgument::REQUIRED, 'Full class name of the desired form class.'),
        );
    }

    /**
     * Get the console command options.
     *
     * @return array
     */
    protected function getOptions()
    {
        return array(
            array('model',null,InputOption::VALUE_OPTIONAL, 'Model where get field to initialize'),
            array('fields', null, InputOption::VALUE_OPTIONAL, 'Fields for the form'),
            array('namespace', null, InputOption::VALUE_OPTIONAL, 'Class namespace'),
            array('path', null, InputOption::VALUE_OPTIONAL, 'File path')
        );
    }

    /**
     * Replace the class name for the given stub.
     *
     * @param  string $stub
     * @param  string $name
     * @return string
     */
    protected function replaceClass($stub, $name)
    {
        $formGenerator = $this->formGenerator;

        $stub = str_replace(
            '{{class}}',
            $formGenerator->getClassInfo($name)->className,
            $stub
        );

        if($this->option('model') != null){
            return str_replace(
                '{{fields}}',
                $formGenerator->getModelVariable($this->option('model')),
                $stub
            );
        }
        else{
            return str_replace(
                '{{fields}}',
                $formGenerator->getFieldsVariable($this->option('fields')),
                $stub
            );
        }


    }

    /**
     * Replace the namespace for the given stub.
     *
     * @param  string $stub
     * @param  string $name
     * @return $this
     */
    protected function replaceNamespace(&$stub, $name)
    {
        $path = $this->option('path');
        $namespace = $this->option('namespace');

        if (!$namespace) {
            $namespace = $this->formGenerator->getClassInfo($name)->namespace;

            if ($path) {
                $namespace = str_replace('/', '\\', trim($path, '/'));
            }
        }

        $stub = str_replace('{{namespace}}', $namespace, $stub);

        return $this;
    }

    /**
     * Get the stub file for the generator.
     *
     * @return string
     */
    protected function getStub()
    {
        return __DIR__ . '/stubs/form-class-template.stub';
    }

    /**
     * Get the desired class name from the input.
     *
     * @return string
     */
    protected function getNameInput()
    {
        return str_replace('/', '\\', $this->argument('name'));
    }

    /**
     * @inheritdoc
     */
    protected function getPath($name)
    {
        $optionsPath = $this->option('path');

        if ($optionsPath !== null) {
            return join('/', [
                $this->laravel->basePath(),
                trim($optionsPath, '/'),
                $this->getNameInput().'.php'
            ]);
        }

        return parent::getPath($name);
    }
}
