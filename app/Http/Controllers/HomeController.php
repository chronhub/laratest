<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use DB;
use Str;
use Illuminate\Support\Facades\Schema;
use function microtime;

final class HomeController
{
    public function __invoke()
    {
        // $this->createTable();
        $this->insert();

        //dd($this->fetch());

        return 'ok';
    }

    private function fetch(): array
    {
        $res = DB::select('select * from ulids order by id desc limit 10');

        return $res;
    }

    private function insert(): void
    {
        //$start = microtime(true);
        $i = 1;
        while ($i !== 5001) {
            DB::insert('insert into ulids (text) values (?)', [Str::random()]);
            $i++;
        }

        // $time = microtime(true) - $start;
    }

    private function createTable(): void
    {
        Schema::getConnection()->getPdo()->query(
            'CREATE TABLE ulids(
                  id TEXT NOT NULL DEFAULT generate_ulid(),
                  position BIGINT NOT NULL,
                  text TEXT NOT NULL,
                  PRIMARY KEY (id)
                );'
        );
    }
}
