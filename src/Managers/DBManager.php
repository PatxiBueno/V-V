<?php

namespace TwitchAnalytics\Managers;

interface DBManager
{
    public function getConnection();
    public function query(string $sql);
    public function prepare(string $sql);
    public function escape(string $value): string;
}