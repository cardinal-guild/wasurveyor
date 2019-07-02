<?php

namespace App\Utils;

use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\DBAL\Exception\RetryableException;
use Doctrine\ORM\EntityManagerInterface;


class CustomEntityManager
{
    private $em;
    private $mr;

    public function __construct(EntityManagerInterface $em,
                                ManagerRegistry $mr)
    {
        $this->em = $em;
        $this->mr = $mr;
    }

    public function transactional(callable $callback)
    {
//	    $this->beginTransaction();
//	    $ret = $callback();
//	    return $ret;
        $retries = 0;
        do {
            $this->beginTransaction();

            try {
                $ret = $callback();

                $this->flush();
                $this->commit();

                return $ret;
            } catch (RetryableException $e) {
                $this->rollback();
                $this->close();
                $this->resetManager();

                ++$retries;
            } catch (\Exception $e) {
                $this->rollback();
                throw $e;
            }
        } while ($retries < 10);
        throw $e;
    }

    public function resetManager()
    {
        $this->em = $this->mr->resetManager();
    }

    public function __call($name, $args) {
        return call_user_func_array([$this->em, $name], $args);
    }
}
