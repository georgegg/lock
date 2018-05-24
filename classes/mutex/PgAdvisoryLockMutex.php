<?php

namespace malkusch\lock\mutex;

use malkusch\lock\exception\LockAcquireException;
use malkusch\lock\exception\TimeoutException;

class PgAdvisoryLockMutex extends LockMutex
{
    /**
     * @var \PDO
     */
    private $pdo;

    /**
     * @var int
     */
    private $key1;

    /**
     * @var int
     */
    private $key2;

    public function __construct(\PDO $PDO, $name)
    {
        $this->pdo = $PDO;

        list($bytes1, $bytes2) = str_split(hash("sha256", $name, true), 4);

        $this->key1 = unpack("i", $bytes1)[1];
        $this->key2 = unpack("i", $bytes2)[1];
    }

    public function lock()
    {
        $statement = $this->pdo->prepare("SELECT pg_advisory_lock(?,?)");

        $statement->execute([
            $this->key1,
            $this->key2,
        ]);
    }

    public function unlock()
    {
        $statement = $this->pdo->prepare("SELECT pg_advisory_unlock(?,?)");
        $statement->execute([
            $this->key1,
            $this->key2
        ]);
    }
}
