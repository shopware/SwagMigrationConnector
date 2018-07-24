<?php

namespace SwagMigrationApi\Service;

class AbstractApiService
{
    /**
     * @param array $data
     * @param array $result
     *
     * @return array
     */
    protected function mapData(array $data, $result = [])
    {
        foreach ($data as $key => $value) {
            if (is_array($value)) {
                $result[$key] = $this->mapData($value, []);
            }
            if (strpos($key, '.') !== false) {
                $chunk = explode('.', $key);
                if (!array_key_exists($chunk[0], $result)) {
                    $result[$chunk[0]] = [];
                }
                $result[$chunk[0]][$chunk[1]] = $value;
            }
        }

        return $result;
    }
}