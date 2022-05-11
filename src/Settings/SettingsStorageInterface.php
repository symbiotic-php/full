<?php

namespace Symbiotic\Settings;


interface SettingsStorageInterface
{
   public function set(string $name, array $data);

   public function get(string $name):array;

   public function has(string $name):bool;

   public function remove(string $name);
}