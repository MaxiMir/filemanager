<?
{
	"autoload": {
		"psr-4": {
			"src\\": "Filemanager";  
		}
	}
}

# https://phpprofi.ru/blogs/post/52

composer self-update // обновление composer
composer dump-autoload --optimize // обновить загрузчик, т. к. появились новые классы без установки или обновления пакетов . Ключ --optimize преобразует PSR-0 в автозагрузку как для classmap, чтобы автозагрузчик был наиболее быстрым. Это настоятельно рекомендуется для production (вы можете получить 20% прирост).

// запись сеттеров в 1 строку:

Class User 
{
	private $name;
	private $age;

	public function __construct(string $name, string $age) 
	{
		$this->name = $name;
		$this->age = $age;
	}

	public function setName(string $name): User
	{
		$this->name = $name;
		return $this;
	}

	public function setAge(string $age): User
	{
		$this->age = $age;
		return $this;
	}	
}

$user = new User('Max', '21');
$user->setName('Jorn')->setAge('25');

interface PersonInterface // указываем только публичные методы
{
	public function get(): string;
	public function set(string $name);
}

interface CityInterface
{
	public function addPerson(Person $person);
	public function getPerson(): array;
}

class Person implements PersonInterface
{
	private $name;

	public function get(): string
	{
		return $this->name;
	}

	public function set(string $value)
	{
		$this->name = $value;
	}
}

class City implements CityInterface
{
	private $persons = [];

	public function addPerson(Person $person)
	{
		$this->persons[] = $person->get(); 
	}

	public function getPerson(): array
	{
		return $this->persons;
	}
}

$person = new Person();
$person->set('Misha');

$person2 = new Person();
$person2->set('Vanya');

$city = new City();
$city->addPerson($person);
$city->addPerson($person2);
$city->getPerson(); // => [0]=> 'Misha', [1]=>'Vanya'


abstract class BaseModel 
{
		public function selectAll(): string
		{
			return 'SELECT * FROM ' . $this->getTableName();
		}

		public function db(string $sql)
		{
			// TODO: db реализацию сделать!
		}

		abstract public function getTableName():string // у потомков реализация данного метода отличается
}

class Article extends BaseModel
{
	public function getTableName(): string
	{
		return 'task';
	}
}

$task = new Atricle();
echo $task->selectAll();

// трейд - механизм, реализующий повторное использование кода. Решает проблему отсутствия множественного наследования.

class Article
{
	public $sql;
	public function executeSql() // реализация методов идентична
	{
		return $this->sql;
	}	
}

class User
{
	public $sql;
	public function executeSql() // реализация методов идентична
	{
		return $this->sql;	
	}
}

// Реализация метода executeSql в классах индентична, а значит можно использовать трейт:

trait BaseModel
{
	public $sql;
	public function executeSql() // реализация методов идентична
	{
		return $this->sql;	
	}

	public function selectAllFromDB()
	{
		$this->sql = 'SELECT * FROM' . $this->getTableName();
	}	

	abstruct public function getTableName(): string;
}

class Article
{
	use BaseModel; // можно перечислить несколько трейтов через ','
	public function getTableName()
	{
		return 'articles';
	}
}

class User 
{
	use BaseModel;
	public function getTableName()
	{
		return 'users';
	}
	
}

$article = new Article();
$article->selectAllFromDB();
echo $article->executeSql(); // => SELECT * FROM articles

$user = new User();
$user->selectAllFromDB();
echo $user->executeSql(); // => SELECT * FROM users


require 'vendor/autoload.php';

class User
{
	public $sql;

	public function addUser(string $login, string $password)
	{
		if(strlen($login) < 3 || strlen($login) > 15) {
			return false;
		}

		if(strlen($password) < 3 || strlen($password) > 6) {
			return false;
		}

		$this->sql = "INSERT INTO users VALUES('', {$login}, {$pasword})";

		return true;
	}
}

$user = new User();
$result = $user->addUser('Misha', '1234');
if ($result === true) {
	echo 'user was added';
} else {
	echo 'fail';
}

// Тоже самое использую Exceptions:

class User
{
	public $sql;

	public function addUser(string $login, string $password)
	{
		if(strlen($login) < 3 || strlen($login) > 15) {
			throw new Exception('Wrong login');
		}
		
		if(strlen($password) < 3 || strlen($password) > 6) {
			hrow new Exception('Wrong password');
		}

		$this->sql = "INSERT INTO users VALUES('', {$login}, {$pasword})";
		
		return true;
	}
}

try {
	$user = new User();
	$result = $user->addUser('Misha', '1234');
	echo 'user was added';	
} catch(Exception $e) {
	die('Fail');
}


// Чтобы каждый раз не писать конструкцию try/catch лучше создать отдельный класс:

class UserLoginException extends Exception
{
	
}

class UserPasswordException extends Exception
{
	
}

class User
{
	public $sql;

	public function addUser(string $login, string $password)
	{
		if(strlen($login) < 3 || strlen($login) > 15) {
			throw new UserLoginException;
		}

		if(strlen($password) < 3 || strlen($password) > 6) {
			throw new UserPasswordException;
		}

		$this->sql = "INSERT INTO users VALUES('', {$login}, {$pasword})";
		
		return true;
	}
}

try {
	$user = new User();
	$result = $user->addUser('Misha', '1234');
	echo 'user was added';	
} catch(UserLoginException $e) {
	die('Wrong login');
} catch(UserPasswordException $e) {
	die('Wrong password');
}


// В этом варианте получается много catch, поэтому:

class UserException extends Exception
{
    
}

class UserLoginException extends UserException
{
	$protected $message = 'wrong login';
}

class UserPasswordException extends UserException
{
	$protected $message = 'wrong password';	
}

class User
{
	public $sql;

	public function addUser(string $login, string $password)
	{
		if(strlen($login) < 3 || strlen($login) > 15) {
			throw new UserLoginException;
		}

		if(strlen($password) < 3 || strlen($password) > 6) {
			throw new UserPasswordException;
		}

		$this->sql = "INSERT INTO users VALUES('', {$login}, {$pasword})";
		
		return true;
	}
}

try {
	$user = new User();
	$result = $user->addUser('Misha', '1234');
	echo 'user was added';	
} catch(UserException $e) {
	die($e->getMessage());
}


PHP PDO: Работа с базой данных
#  Соединение с базой данных

namespace Theory

$opt = [
	\PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION	// режим ошибок - Exceptions	
	   ];

/* $dsn = "mysql:host=$host;dbname=$db;charset=$charset"; // Формат описывающий параметры для подключения */

$pdo = new \PDO('sqlite::memory', null, null, $opt); // 2-й, 3-й параметр логин и пароль.

$pdo->exec("create table users (id integer, name string)");	   
$pdo->exec("insert into users values(3, 'adel')");	   
$pdo->exec("insert into users (7, 'ada')");	   
$data = $pdo->query("select * from users")->fetchAll();
print_r($data);




/**
Реализуйте интерфейс App\DDLManagerInterface в классе App\DDLManager

Пример использования:
**/

$dsn = 'sqlite::memory:';
$ddl = new DDLManager($dsn);

$ddl->createTable('users', [
    'id' => 'integer',
    'name' => 'string'
]);

// Получившийся запрос в базу:

CREATE TABLE users (
    id integer,
    name string
);



namespace App;

interface DDLManagerInterface
{
    public function __construct($dsn, $user = null, $pass = null);

    public function createTable($table, array $params);
}


namespace App;

class DDLManager implements DDLManagerInterface
{
    private $pdo;

    public function __construct($dsn, $user = null, $pass = null)
    {
        $options = [\PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION];
        $this->pdo = new \PDO($dsn, $user, $pass, $options);
    }

    public function createTable($table, array $params)
    {
        $fieldParts = array_map(function ($key, $value) {
            return "{$key} {$value}";
        }, array_keys($params), $params);
        $fieldsDescription = implode(", ", $fieldParts);
        $sql = sprintf("CREATE TABLE %s (%s)", $table, $fieldsDescription);
        return $this->pdo->exec($sql);
    }  

    public function getConnection()
    {
        return $this->pdo;
    }
}

#  Безопасность при работе с внешними данными

// WRONG!!!

$id = 7;
$name = 'ada';
$pdo->exec("insert into users values ($id, '$name')");

// SQL INJECTION:
/* $name = 'ada'); DELETE FROM users; --"; */
/* $sql = "insert into users values ($id, '$name')"; */
/* print_r($sql); */
/* $pdo->exec($sql); */


/* $values = [3, 'm\'ark --']; */
/* $data = implode(', ', array_map(function ($item) use ($pdo) { */
/*	return $pdo->quote($item); */
/* }, $values); */

$data = $pdo->query("select * from users")->fetchAll();