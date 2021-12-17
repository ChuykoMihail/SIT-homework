<?php

namespace App\Service;

class ShowEnv{
    private $curr_env;

    public function __construct(string $curr_env)
    {
        $this->curr_env = $curr_env;
    }
    public function getEnv(): string{
        return $this->curr_env;
    }
}