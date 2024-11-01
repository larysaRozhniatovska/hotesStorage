<?php
namespace app\lib;
class UserManager {
    private string $user = "";
    /**all added users
     * @var array
     */
    private array $users = [];
    private array $errors = [];
    private string $fileDir = "storage";
    private string $file = "users.json";
    private bool $isChange = false;
    public function __construct()
    {
        $this->fileDir = realpath($this->fileDir);
        if (!file_exists($this->fileDir)){
            mkdir($this->fileDir);
        }
        $this->file = $this->fileDir . DIRECTORY_SEPARATOR . $this->file;
        $this->loadUsers();
    }
    public function __destruct()
    {
       $this->saveUsers();
    }

    /**
     * save all Users in file storage/users.json
     * @return void
     */
    public function saveUsers():void
    {
        if($this->isChange){
            $jsonUser = json_encode($this->users);
            if(!file_put_contents($this->file, $jsonUser)){
                $this->errors[] = "error writing to file";
            }
        }
    }

    /**
     * return errors
     * @return array
     */
    public function getErrors(): array
    {
        return $this->errors;
    }

    /**
     * add  User in array $this->users
     * @param array $user
     * @return void
     */
    public function addUser(array $user) : void
    {
        $hash = md5(serialize($user['login']));
        $hash_pass = password_hash($user['password'], PASSWORD_DEFAULT);
        $user['password'] = $hash_pass;
        $this->users[$hash] = $user;
        $this->isChange = true;
    }

    /**
     * read info all Users from file
     * @return void
     */
    private function loadUsers() :void
    {
        if (file_exists($this->file)) {
            $jsonUsers = file_get_contents($this->file);
            if ($jsonUsers) {
                $users = json_decode($jsonUsers, true);
                if (!$users) {
                    $this->errors[] = "data decoding error";
                } else {
                    $this->users = $users;
                }
            }else{
                $this->errors[] = "error reading from file";
            }
        }
    }

    /**
     * returns logins of all users
     * @return array
     */
    public function getLoginUsers(): array
    {
        $logins = array_column($this->users, "login", );
        return array_filter($logins, function ($val){
            return $val !== "";
        });
    }
    /**
     * Checking for user presence by login
     * @param string $login
     * @return bool
     */
    public function existUser(string $login): bool
    {
        $hash = md5(serialize($login));
        return key_exists($hash, $this->users);
    }

    /**
     * user validation upon registration
     * @param string $login
     * @return array|string[]
     */
    public function validationAddUser(string $login): array
    {
        if ($this->existUser($login)){
            return ['User ' . $login . ' is already registered'];
        }

        return [];
    }

    /**
     * user validation on login
     * @param string $login
     * @param string $password
     * @return array|string[]
     */
    public function validationLoginUser(string $login, string $password): array
    {
        if (!$this->existUser($login)){
            return ['User ' . $login . ' is not registered'];
        }
        $info = $this->infoUser($login);
        if (!empty($info)){
            if (!password_verify($password, $info['password'])) {
                return ['The password is incorrect'];
            }
        }else{
            return ['User ' . $login . ' is not registered'];
        }

        return [];
    }
    /**
     * returns user information by login
     * @param string $login
     * @return array
     */
    public function infoUser(string $login): array
    {
        if ($login === "")
            return [];
        $result = array_filter(
            $this->users,
            function ($user) use ($login) {
                return $user["login"] === $login;
            },
        );
        $result = reset($result);
        if ($result["login"] === $login) {
            return $result;
        }else {
            return [];
        }
    }
};
