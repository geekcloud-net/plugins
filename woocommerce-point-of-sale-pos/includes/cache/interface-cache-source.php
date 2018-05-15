<?php

/**
 * Interface CacheSource cache source
 */
interface CacheSource
{
    public function saveProducts($products);

    public function loadProducts($products);
}