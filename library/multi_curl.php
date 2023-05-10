<?
    /**
* Simple wrapper function for concurrent request processing with PHP's cURL functions (i.e., using curl_multi* functions.)
*
* @param array $requests Array containing request url, post_data, and settings.
* @param array $opts Optional array containing general options for all requests.
* @return array Array containing keys from requests array and values of arrays each containing data (response, null if response empty or error), info (curl info, null if error), and error (error string if there was an error, otherwise null).
*/
function multi(array $requests, array $opts = [])
{
    // create array for curl handles
    $chs = [];
    // merge general curl options args with defaults
    $opts += [CURLOPT_CONNECTTIMEOUT => 3, CURLOPT_TIMEOUT => 3, CURLOPT_RETURNTRANSFER => 1];
    // create array for responses
    $responses = [];
    // init curl multi handle
    $mh = curl_multi_init();
    // create running flag
    $running = null;
    // cycle through requests and set up
    foreach ($requests as $key => $request) {
        // init individual curl handle
        $chs[$key] = curl_init();
        // set url
        curl_setopt($chs[$key], CURLOPT_URL, $request['url']);
        // check for post data and handle if present
        if (array_key_exists('post_data', $request)) {
            curl_setopt($chs[$key], CURLOPT_POST, 1);
            curl_setopt($chs[$key], CURLOPT_POSTFIELDS, $request['post_array']);
        }
        // set opts 
        curl_setopt_array($chs[$key], (isset($request['opts']) ? $request['opts'] + $opts : $opts));
        curl_multi_add_handle($mh, $chs[$key]);
    }
    do {
        // execute curl requests
        curl_multi_exec($mh, $running);
        // block to avoid needless cycling until change in status
        curl_multi_select($mh);
    // check flag to see if we're done
    } while($running > 0);
    // cycle through requests
    foreach ($chs as $key => $ch) {
        // handle error
        if (curl_errno($ch)) {
            $responses[$key] = ['data' => null, 'info' => null, 'error' => curl_error($ch)];
        } else {
            // save successful response
            $responses[$key] = ['data' => curl_multi_getcontent($ch), 'info' => curl_getinfo($ch), 'error' => null];
        }
        // close individual handle
        curl_multi_remove_handle($mh, $ch);
    }
    // close multi handle
    curl_multi_close($mh);
    // return respones
    return $responses;
}
?>