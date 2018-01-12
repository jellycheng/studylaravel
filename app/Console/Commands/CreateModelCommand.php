<?php namespace App\Console\Commands;

use Illuminate\Console\Command;
use DB;
//在App\Console\Kernel.php中配置$commands属性值，$commands=['App\Console\Commands\CreateModelCommand', 其它命令]
class CreateModelCommand extends Command {

	/**
	 * The console command name.
     * 命令名
	 * @var string
	 */
	protected $name = 'create:appmodel';

	/**
	 * The console command description.
	 * @var string
	 */
	protected $description = 'create app model';

	/**
	 * Execute the console command.
     * 这里写业务逻辑
	 * @return mixed
	 */
	public function handle()
	{
        $dbconnection = 'mysql'; //db connection
        $tables = $this->getAllTables($dbconnection);
        if($tables){
            $template = file_get_contents(dirname(__DIR__) . '/stubs/model.stub');
            $modelPath = app_path() . '/Model/';
            foreach ($tables as $k=>$table) {
                $file = $modelPath . $this->parseNmae($table) . '.php';
                if(!file_exists($file)) {
                    $content = str_replace(['{{$className}}','{{$connection}}', '{{$table}}', '{{$primaryKey}}'],
                                            [$this->parseNmae($table), $dbconnection, $table, 'id'],
                                            $template);
                    file_put_contents($file, $content);
                    $this->comment(PHP_EOL.$dbconnection . ':' . $file.PHP_EOL);
                }

            }
        }

	}

	protected function parseNmae($name) {
        $line = preg_replace_callback(
            '/_([a-zA-Z])/',
            function ($matches) {
                return strtoupper($matches[1]);
            },
            $name
        );
        return ucfirst($line);
    }


	protected function getAllTables($dbconnection)  {
        $tables = [];
        $tblSql = "show tables";
        $pdo = DB::connection($dbconnection)->getPdo();
        $sth = $pdo->prepare($tblSql);
        $sth->execute();
        $result = $sth->fetchAll(\PDO::FETCH_ASSOC);
        if($result){
            foreach ($result as $k=>$value) {
                $tmp = array_values($value);
                $tables[] = $tmp[0];
            }
        }
        return $tables;
    }

}
