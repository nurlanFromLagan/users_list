<?php

namespace App\model;

use Aura\SqlQuery\QueryFactory;
use PDO;


class QueryBuilder
{

    private $queryFactory;
    private $pdo;

    public function __construct(QueryFactory $queryFactory, PDO $pdo)
    {
        $this->queryFactory = $queryFactory;
        $this->pdo = $pdo;
    }

    /*
     * getAll - фунция для получения всех данных из таблицы в БД, возвращает массив всех строк
     * string $table - имя таблицы в БД, строковый тип данных
     * */
    public function getAll (string $table) {

        $select = $this->queryFactory->newSelect();
        $select->cols(['*'])
                ->from($table);


        // prepare the statement
        $sth = $this->pdo->prepare($select->getStatement());

        // bind the values and execute
        $sth->execute($select->getBindValues());

        // get the results back as an associative array
        $result = $sth->fetchAll(PDO::FETCH_ASSOC);

        return $result;
    }


    /*
     * insert - фунция для добавления данных в таблицу в БД
     * string $table - имя таблицы в БД, тип данных строка (хотя долго сомневался ставить тип string, может ли название таблицы состоять только из цифр и быть int
     * array $data - массив с названиями стлобцов таблицы, тип данных массив
     * */
    public function insert (string $table, array $data) {

        $insert = $this->queryFactory->newInsert();

        $insert->into($table)             // insert into this table
        ->cols($data);

        // prepare the statement
        $sth = $this->pdo->prepare($insert->getStatement());

        // execute with bound values
        $sth->execute($insert->getBindValues());

    }


    /*
     * update - фунция для сохранения внесенных изменений в БД
     * string $table - имя таблицы в БД, тип данных строка
     * array $data - массив с названиями стлобцов таблицы, тип данных массив
     * */
    public function update (string $table, array $data, int $id) {

        $update = $this->queryFactory->newUpdate();

        $update
            ->table($table)                  // update this table
            ->cols($data)
            ->where('id = :id')
            ->bindValues([                  // bind these values to the query
                'id' => $id
            ]);

        // prepare the statement
        $sth = $this->pdo->prepare($update->getStatement());

        // execute with bound values
        $sth->execute($update->getBindValues());
    }


    /*
     * delete - функция для удаления данных из БД, удаляет всю строку по id
     * string $table - имя таблицы в БД, строковый тип данных
     * int id - id строки, тип данных integer
     * */
    public function delete (string $table, int $id) {

        $delete = $this->queryFactory->newDelete();

        $delete
            ->from($table)                   // FROM this table
            ->where('id = :id')           // AND WHERE these conditions
            ->bindValues([                  // bind these values to the query
                'id' => $id
            ]);

        // prepare the statement
        $sth = $this->pdo->prepare($delete->getStatement());

        // execute with bound values
        $sth->execute($delete->getBindValues());
    }


    /*
     * getOne - функция для получения данных по id из БД, возвращает массив строки
     * string $table - имя таблицы в БД, тип данных строка
     * int id - id строки, тип данных integer
     * */
    public function getOne (string $table, int $id) {

        $select = $this->queryFactory->newSelect();
        $select->cols(['*'])
            ->from($table)
            ->where('id = :id')
            ->bindValues([
                'id' => $id
            ]);


        // prepare the statement
        $sth = $this->pdo->prepare($select->getStatement());

        // bind the values and execute
        $sth->execute($select->getBindValues());

        // get the results back as an associative array
        $result = $sth->fetchAll(PDO::FETCH_ASSOC);

        return $result;
    }


    /*
     * setPaging - функция для установки пагинации
     * string $table - имя таблицы в БД, тип данных строка
     * int $paging - количество пользователей получаемых из базы
     * return int
     * */
    public function setPaging($table, $paging) {

        $select = $this->queryFactory->newSelect();

        $select->cols(['*'])
            ->from($table)
            ->setPaging($paging)
            ->page($_GET['page'] ?? 1);

        // prepare the statement
        $sth = $this->pdo->prepare($select->getStatement());

        // bind the values and execute
        $sth->execute($select->getBindValues());

        // get the results back as an associative array
        $result = $sth->fetchAll(PDO::FETCH_ASSOC);

        return $result;

    }


}