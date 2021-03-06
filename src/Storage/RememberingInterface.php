<?php

namespace Symbiotic\Storage;

interface RememberingInterface
{
    /**
     * Совмещенный метод получения и записи данных
     *
     * Если в вашем хранилище найдутся данные по ключу, то надо их вернуть
     * Если нет данных, то выполнить функцию, записать данные в хранилище с ключом и вернуть.
     *
     * Как ваше хранилище работает и есть ли оно вообще, решать вам.
     * Можете обмануть систему и просто возвращать результат функции)))
     *
     * @param string $key ключ в хранилище
     * @param callable $value функция для получения данных
     * @return mixed
     *
     * @throws \Throwable - если не удается записать
     */
    public function remember(string $key, callable $value);
}