<?php

namespace app\lib;

class Notes
{
    private string $username;
    private array $notes = [];
    private bool $isChange = false;
    private array $errors = [];
    private string $fileDir = "storage";
    private string $file = "notes.json";

    public function __construct()
    {
        $this->fileDir = realpath($this->fileDir);
        if (!file_exists($this->fileDir)){
            mkdir($this->fileDir);
        }
        $this->file = $this->fileDir . DIRECTORY_SEPARATOR . $this->file;
        $this->loadNotes();
    }
    public function __destruct()
    {
        $this->saveNotes();
    }
    /**
     * read info all Notes for all Users from file
     * @return void
     */
    private function loadNotes() : void
    {
        if (file_exists($this->file)) {
            $jsonNotes = file_get_contents($this->file);
            if ($jsonNotes) {
                $notes = json_decode($jsonNotes, true);
                if (!$notes) {
                    $this->errors[] = "data decoding error";
                } else {
                    $this->notes = $notes;
                }
            }else{
                $this->errors[] = "error reading from file";
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
     * add note for the User in array $this->notes
     * @param string $user
     * @param string $note
     * @return void
     */
    public function addNote(string $user, string $note): void
    {
        $hash = md5(serialize($user));
        $this->notes[$hash][] = $note;
        $this->isChange = true;
    }
    /**
     * save notes for all Users in file storage/notes.json
     * @return void
     */
    public function saveNotes():void
    {
        if($this->isChange){
            $jsonNotes = json_encode($this->notes);
            if(!file_put_contents($this->file, $jsonNotes)){
                $this->errors[] = "error writing to file";
            }
        }
    }
    /**
     * returns user notes by login
     * @param string $user
     * @return array
     */
    public function notesUser(string $user): array
    {
        if ($user === "")
            return [];
        $hash = md5(serialize($user));
        $result = array_filter(
            $this->notes,
            function ($key) use ($hash) {
                return $key === $hash;
            },ARRAY_FILTER_USE_KEY
        );
        $result = reset($result);
        if ($result === false){
            return [];
        }else {
            return $result;
        }
    }
    /**
     * delete note for user by login &login
     * @param string $user
     * @param int $id
     * @return void
     */
    public function delNote(string $user, int $id): void
    {
        var_dump($this->notes);
        if ($user === "")
            return ;
        $hash = md5(serialize($user));
        $result = array_filter(
            $this->notes,
            function ($key) use ($hash) {
                return $key === $hash;
            },ARRAY_FILTER_USE_KEY
        );
        $result = reset($result);
        if (($result !== false) && (count($result) > $id)){
            unset($result[$id]);
            if (!array_is_list($result)){
                $keys = [];
                foreach ($result as $item){
                    $keys[] = $item;
                }
                $this->notes[$hash] = $keys;
            }else{
                $this->notes[$hash] = $result;
            }
            $this->isChange = true;
        }
    }


}