<?php  namespace Kris\LaravelFormBuilder\Console;

use Illuminate\Support\Facades\DB;

class FormGenerator
{

    private $binding_type = [
        'int'       =>  'number',
        'varchar'   =>  'text',
        'text'      =>  'textarea',
        'tinyint'   =>  'checkbox',
        'timestamp'   =>  'text',
        'enum'   =>  'select',
    ];

    /**
     * Get fields from options and create add methods from it
     *
     * @param null $fields
     * @return string
     */
    public function getFieldsVariable($fields = null)
    {
        if ($fields) {
            return $this->parseFields($fields);
        }

        return '// Add fields here...';
    }

    private function parseDbType($dbType){
        $type = explode('(', $dbType);
        return $this->binding_type[$type[0]];
    }

    public function getModelVariable($model = null, $model_namespace="App\\", $db_=false){

        $result = "";
        $model = $model_namespace."\\".$model;
        $modelObject = new $model;

        $db = 'mysql';
        if($db_ != false){
            $db = $db_;
        }

        $cols = [];

        $table_info_columns = DB::connection($db)->select( DB::raw('SHOW COLUMNS FROM '.$modelObject->getTable()));

        foreach($table_info_columns as $column){
            $result .= $column->Field . ':' . $this->parseDbType($column->Type) . ',';
        }

        $result = rtrim($result, ",");

        return $this->parseFields($result);
    }

    /**
     * @param string $name
     * @return object
     */
    public function getClassInfo($name)
    {
        $explodedClassNamespace = explode('\\', $name);
        $className = array_pop($explodedClassNamespace);
        $fullNamespace = join('\\', $explodedClassNamespace);

        return (object)[
            'namespace' => $fullNamespace,
            'className' => $className
        ];
    }

    /**
     * Parse fields from string
     *
     * @param $fields
     * @return string
     */
    protected function parseFields($fields)
    {
        $fieldsArray = explode(',', $fields);
        $text = '$this'."\n";

        foreach ($fieldsArray as $field) {
            $text .= $this->prepareAdd($field, end($fieldsArray) == $field);
        }

        return $text.';';
    }

    /**
     * Prepare template for single add field
     *
     * @param      $field
     * @param bool $isLast
     * @return string
     */
    protected function prepareAdd($field, $isLast = false)
    {
        $field = trim($field);
        list($name, $type) = explode(':', $field);
        $textArr = [
            "            ->add('",
            $name,
            "', '",
            $type,
            "')",
            ($isLast) ? "" : "\n"
        ];

        return join('', $textArr);
    }

}
