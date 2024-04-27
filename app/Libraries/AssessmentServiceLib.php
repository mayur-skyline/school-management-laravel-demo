<?php

namespace App\Libraries;

use Error;

class AssessmentServiceLib
{
    private $schoolId = null;

    /**
     * @return AssessmentServiceLib
     */
    public static function make(int $schoolId = null): AssessmentServiceLib
    {

        return new self($schoolId);
    }

    public function __construct(int $schoolId = null)
    {
        $this->schoolId = $schoolId;
    }

    /**
     * Create a new assessment main entry record.
     *
     * @param array $data_array
     * @return array
     */
    public function createMain(array $data_array): array
    {
        $options = array(
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => json_encode($data_array),
            CURLOPT_HTTPHEADER => array(
                'Content-Type: application/json'
            ),
        );
        $request = new URLService($this->withCurlOptions('assessment', $options));
        $response = $request->exec();

        return $this->responseHandler($response);
    }

    /**
     * Get an assessment main by its ID.
     *
     * @param string $id
     * @return array
     */
    public function getMainById(string $id): array
    {
        $options = array(CURLOPT_CUSTOMREQUEST => 'GET');
        $request = new URLService($this->withCurlOptions("assessment/{$id}", $options));
        $response = $request->exec();

        return $this->responseHandler($response);
    }

    /**
     * Delete an assessment main record by its unique ID.
     * @param string $id firestore ID.
     * @return array
     */
    public function deleteMainById(string $id): array
    {
        $options = array(CURLOPT_CUSTOMREQUEST => 'DELETE');
        $request = new URLService($this->withCurlOptions("assessment/{$id}", $options));
        $response = $request->exec();

        return $this->responseHandler($response);
    }

    /**
     * Set the `is_completed` status of an assessment record to `Y` when called.
     * This should be called when all sections of assessment are completed.
     *
     * @param $id firestore ID.
     * @return array
     */
    public function completeMainById($id): array
    {
        $options = array(CURLOPT_CUSTOMREQUEST => 'PATCH');
        $request = new URLService($this->withCurlOptions("assessment/{$id}", $options));
        $response = $request->exec();

        return $this->responseHandler($response);
    }

     /**
     * Update the `is_manipulated` value of an assessment main
     * @param $id
     * @return array
     */
    public function updateMainManipulationById($id): array
    {
        $options = array(
            CURLOPT_CUSTOMREQUEST => 'PATCH',
            CURLOPT_POSTFIELDS => json_encode(['is_manipulated' => true]),
            CURLOPT_HTTPHEADER => array(
                'Content-Type: application/json'
            ),
        );
        $request = new URLService($this->withCurlOptions("assessment/{$id}", $options));
        $response = $request->exec();

        return $this->responseHandler($response);
    }

    /**
     * Get the rawdata and tracking information of an assessment.
     * Rawdata and trackings are combined into array.
     *
     * @param string $id firestore ID
     * @return array
     */
    public function getTrackerById(string $id): array
    {
        $options = array(CURLOPT_CUSTOMREQUEST => 'GET');
        $request = new URLService($this->withCurlOptions("tracker/{$id}", $options));
        $response = $request->exec();

        return $this->responseHandler($response);
    }

    /**
     * Create a new tracker object (rawdata and tracking) for an assessment main entry
     * if it does not exist already.
     *
     * @param string $id
     * @param array $data
     * @return array
     */
    public function createTracker(string $id, array $data): array
    {
        $options = array(
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => json_encode($data),
            CURLOPT_HTTPHEADER => array(
                'Content-Type: application/json'
            ),
        );
        $request = new URLService($this->withCurlOptions("tracker/{$id}", $options));
        $response = $request->exec();

        return $this->responseHandler($response);
    }

    /**
     * Update the tracker object (rawdata and tracking), this is used for synchronizing the
     * score information of pupil.
     *
     * @param string $id
     * @param array $data
     * @return array
     */
    public function updateTrackerById(string $id, array $data): array
    {
        $options = [
            CURLOPT_CUSTOMREQUEST => 'PATCH',
            CURLOPT_POSTFIELDS => json_encode($data),
            CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
        ];
        $request = new URLService($this->withCurlOptions("tracker/{$id}", $options));
        $response = $request->exec();

        return $this->responseHandler($response);
    }

    /**
     * Delete the replies of the specified section in the tracker.
     *
     * @param string $id
     * @param array $data ['type' => 'at']
     * @return array
     */
    public function deleteTrackerSectionById(string $id, array $data) :array
    {
        $options = [
            CURLOPT_CUSTOMREQUEST => 'DELETE',
            CURLOPT_POSTFIELDS => json_encode($data),
            CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
        ];
        $request = new URLService($this->withCurlOptions("tracker/{$id}", $options));
        $response = $request->exec();

        return $this->responseHandler($response);
    }

    /**
     * Update the manipulation text of a tracker sectin to "save" or "repeat".
     *
     * @param string $id
     * @param array $data ['type' => 'at', 'action' => 'repeat']
     * @return array
     */
    public function updateManipulationText(string $id, array $data): array
    {
        $options = [
            CURLOPT_CUSTOMREQUEST => 'PATCH',
            CURLOPT_POSTFIELDS => json_encode($data),
            CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
        ];
        $request = new URLService($this->withCurlOptions("tracker/{$id}/manipulation", $options));
        $response = $request->exec();

        return $this->responseHandler($response);
    }

    /**
     * Get the last assessment taken by the pupil.
     *
     * @param array $data
     * @return array
     */
    public function getLastAssessment(array $data): array
    {
        $options = array(
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => json_encode($data),
            CURLOPT_HTTPHEADER => array(
                'Content-Type: application/json'
            ),
        );
        $request = new URLService($this->withCurlOptions("status", $options));
        $response = $request->exec();
        return $this->responseHandler($response);
    }

    /**
     * Get a portion of tracking object by their QID.
     *
     * @param int $qid
     * @param array $sections
     * @return mixed|null
     */
    public function getSectionByQid(int $qid, array $sections = [])
    {
        for ($i = 0; $i < count($sections); $i++) {
            $section = $sections[$i];
            if ($section['qid'] == $qid) {
                return $section;
            }
        }

        return null;
    }

    /**
     * Helper to handle API response.
     *
     * @param $response
     * @return array
     */
    private function responseHandler($response): array
    {
        $data = json_decode($response);
        $response_data = [];
        if (isset($data->status) && $data->status != "") {
            $response_data['status'] = $data->status;
        } else{
            $response_data['status'] = false;
        }

        $response_data['data'] = isset($data->data) ? json_decode(json_encode($data->data), true) : null;

        return $response_data;
    }

    /**
     * Build up curl options needed for making API requests.
     *
     * @param $url
     * @param array $options
     * @return array
     */

    private function withCurlOptions($url, array $options = []): array
    {
        $namespace = getSchoolNamespace($this->schoolId ?? mySchoolId());
        if (!$namespace) throw new Error('School namespace not defined.');

        $defaultOptions =  array(
            CURLOPT_URL => api_service_url("/{$namespace}/{$url}"),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        );

        if (count($options)) {
            foreach ($options as $key => $value) {
                $defaultOptions[$key] = $value;
            }
        }
        return $defaultOptions;
    }

    public function scoreCount(array $data): array
    {
        $options = array(
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => json_encode($data),
            CURLOPT_HTTPHEADER => array(
                'Content-Type: application/json'
            ),
        );
        $request = new URLService($this->withCurlOptions("score", $options));
        $response = $request->exec();

        return $this->responseHandler($response);
    }

    public function getnextvalidassessment(array $data): array
    {
        $options = array(
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => json_encode($data),
            CURLOPT_HTTPHEADER => array(
                'Content-Type: application/json'
            ),
        );
        $request = new URLService($this->withCurlOptions("status/next-round", $options));
        $response = $request->exec();

        return $this->responseHandler($response);
    }

}
