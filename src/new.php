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

// А значит можно использовать трейт:

trait BaseModel
{
	public $sql;
	public function executeSql() // реализация методов идентична
	{
		return $this->sql;	
	}
}

class Article
{
	use BaseModel; // можно перечислить несколько трейтов через ','
	public function setSql()
	{
		$this->sql = 'SELECT * FROM articles';
	}
}

class User 
{
	use BaseModel;
	public function setSql()
	{
		$this->sql = 'SELECT * FROM users';
	}		
}

$article = new Article();
$article->setSql();
echo $article->executeSql(); // => SELECT * FROM articles

$user = new User();
$user->setSql();
echo $user->executeSql(); // => SELECT * FROM users