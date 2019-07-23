<?php
/**
 * (c) shopware AG <info@shopware.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagMigrationConnector\Service;

abstract class AbstractApiService
{
    /**
     * @param array $data
     * @param array $result
     *
     * @return array
     */
    protected function mapData(array $data, array $result = [], array $pathsToRemove = [])
    {
        foreach ($data as $key => $value) {
            if (is_numeric($key)) {
                $result[$key] = $this->mapData($value, [], $pathsToRemove);
            } else {
                $paths = explode('.', $key);
                $fieldKey = $paths[count($paths) - 1];
                $chunks = explode('_', $paths[0]);

                if (!empty($pathsToRemove)) {
                    $chunks = array_diff($chunks, $pathsToRemove);
                }
                $this->buildArrayFromChunks($result, $chunks, $fieldKey, $value);
            }
        }

        return $result;
    }

    /**
     * @param array  $array
     * @param array  $path
     * @param string $fieldKey
     * @param mixed  $value
     */
    protected function buildArrayFromChunks(array &$array, array $path, $fieldKey, $value)
    {
        $key = array_shift($path);

        if (empty($key)) {
            $array[$fieldKey] = $value;
        } elseif (empty($path)) {
            $array[$key][$fieldKey] = $value;
        } else {
            if (!isset($array[$key]) || !is_array($array[$key])) {
                $array[$key] = [];
            }
            $this->buildArrayFromChunks($array[$key], $path, $fieldKey, $value);
        }
    }

    /**
     * @param array $data
     *
     * @return array
     */
    protected function cleanupResultSet(array &$data)
    {
        foreach ($data as $key => &$value) {
            if (is_array($value)) {
                if (empty(array_filter($value))) {
                    unset($data[$key]);

                    continue;
                }
                $this->cleanupResultSet($value);

                if (empty(array_filter($value))) {
                    unset($data[$key]);

                    continue;
                }
            }
        }

        return $data;
    }
}
