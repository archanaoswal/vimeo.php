<?php
/**
 *   Copyright 2013 Vimeo
 *
 *   Licensed under the Apache License, Version 2.0 (the "License");
 *   you may not use this file except in compliance with the License.
 *   You may obtain a copy of the License at
 *
 *       http://www.apache.org/licenses/LICENSE-2.0
 *
 *   Unless required by applicable law or agreed to in writing, software
 *   distributed under the License is distributed on an "AS IS" BASIS,
 *   WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 *   See the License for the specific language governing permissions and
 *   limitations under the License.
 */
ini_set('display_errors', 'On');
error_reporting(E_ALL);
 
require_once('../vimeo.php');

$config = json_decode(file_get_contents('./config.json'), true);

$lib = new Vimeo($config['client_id'], $config['client_secret'], $config['access_token']);

// Set the number of items to show on each page to 50
$search_results = $lib->request('/videos?per_page=50&query='.urlencode($_GET['query']));

$videos = $search_results['body']['data'];

// Select only the elements from the API call we need
$out=array_map(function($x) {return array('link' => $x['link'], 'name' => $x['name'], 'user' => $x['user']['name']);}, $videos);

// Our filter for only search hits from myusername
class validItems extends FilterIterator
{
    public function accept()
    {
        $current = $this->current();
        if ($current['user'] == 'myusername') {
            return true;
        }
        return false;
    }
}

// Apply the filter on our search results
$available = new validItems(new ArrayIterator($out));

// Create json encoded output
$out = array();
foreach ($available as $value) {
    array_push($out, $value);
}

echo json_encode($out);
