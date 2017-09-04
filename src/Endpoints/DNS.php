<?php
/**
 * Created by PhpStorm.
 * User: junade
 * Date: 09/06/2017
 * Time: 15:14
 */

namespace Cloudflare\API\Endpoints;

use Cloudflare\API\Adapter\Adapter;

class DNS implements API
{
    private $adapter;

    public function __construct(Adapter $adapter)
    {
        $this->adapter = $adapter;
    }

    public function addRecord(
        string $zoneID,
        string $type,
        string $name,
        string $content,
        int $ttl = 0,
        bool $proxied = true
    ): bool {
        $options = [
            'type' => $type,
            'name' => $name,
            'content' => $content,
            'proxied' => $proxied
        ];

        if ($ttl > 0) {
            $options['ttl'] = $ttl;
        }

        $user = $this->adapter->post('zones/' . $zoneID . '/dns_records', [], $options);

        $body = json_decode($user->getBody());

        if (isset($body->result->id)) {
            return true;
        }

        return false;
    }

    public function listRecords(
        string $zoneID,
        string $type = "",
        string $name = "",
        string $content = "",
        int $page = 1,
        int $perPage = 20,
        string $order = "",
        string $direction = "",
        string $match = "all"
    ): \stdClass {
        $options = [
            'page' => $page,
            'per_page' => $perPage,
            'match' => $match
        ];

        if (!empty($type)) {
            $options['type'] = $type;
        }

        if (!empty($name)) {
            $options['name'] = $name;
        }

        if (!empty($content)) {
            $options['content'] = $content;
        }

        if (!empty($order)) {
            $options['order'] = $order;
        }

        if (!empty($direction)) {
            $options['direction'] = $direction;
        }

        $query = http_build_query($options);

        $user = $this->adapter->get('zones/' . $zoneID . '/dns_records?' . $query, []);
        $body = json_decode($user->getBody());

        $result = new \stdClass();
        $result->result = $body->result;
        $result->result_info = $body->result_info;

        return $result;
    }

    public function getRecordDetails(string $zoneID, string $recordID): \stdClass
    {
        $user = $this->adapter->get('zones/' . $zoneID . '/dns_records/' . $recordID, []);
        $body = json_decode($user->getBody());
        return $body->result;
    }

    public function updateRecordDetails(string $zoneID, string $recordID, array $details): \stdClass
    {
        $response = $this->adapter->put('zones/' . $zoneID . '/dns_records/' . $recordID, [], $details);
        return json_decode($response->getBody());
    }

    public function deleteRecord(string $zoneID, string $recordID): bool
    {
        $user = $this->adapter->delete('zones/' . $zoneID . '/dns_records/' . $recordID, [], []);

        $body = json_decode($user->getBody());

        if (isset($body->result->id)) {
            return true;
        }

        return false;
    }

}