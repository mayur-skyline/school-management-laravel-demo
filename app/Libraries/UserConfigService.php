<?php

namespace App\Libraries;


class UserConfigService
{
    /**
     * The Firestore collection name as allowed by the Global-config-service.
     * @var string
     */
    protected string $storeName = 'ast-sessions';

    /**
     * Get user config.
     *
     * @param $user
     * @return \stdClass
     */
    public function get($user): \stdClass
    {
        $response = $this->makeAPIRequest($user);

        return $response->data ??  new \stdClass();
    }

    /**
     * Set user config
     *
     * @param $user
     * @param array $data
     * @return \stdClass
     */
    public function set($user, array $data): \stdClass
    {
        $reqOpt = [
            CURLOPT_POSTFIELDS => json_encode([
            'data' => $data,
            'upsert' => true,
                ]),
            CURLOPT_CUSTOMREQUEST => 'PUT',
        ];

        return $this->makeAPIRequest($user, $reqOpt);
    }

    /**
     * Generate session the unique ID in Firestore collection. (Encrypt data to avoid plainly storing user ID)
     *
     * @param array $pupil
     * @return false|string
     */
    private function makeConfigKey(array $pupil): bool|string
    {
        $id = $pupil['school_id'] . '_' . $pupil['id'];

        return encrypt_decrypt('encrypt', $id);
    }

    /**
     * @param $user
     * @param null $options
     * @return mixed
     */
    private function makeAPIRequest($user, $options = null): mixed
    {
        $config_id =  $this->makeConfigKey(['id' => $user->id, 'school_id' => $user->school_id]);
        $url = api_service_url("/config/{$this->storeName}/{$config_id}");

        $curl_options = $this->curlOptions($url);
        if (is_array($options) && count($options)) {
            foreach ($options as $opt => $value) {
                $curl_options[$opt] = $value;
            }
        }

        $request = new URLService($curl_options);
        $response = $request->exec();

        return json_decode($response);
    }

    /**
     * @param string $url
     * @param string $method
     * @return array
     */
    private function curlOptions(string $url, string $method = 'GET'): array
    {
        return [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => $method,
            CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
        ];
    }
}
