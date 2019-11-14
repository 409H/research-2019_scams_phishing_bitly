<?php
require_once __DIR__ ."/vendor/autoload.php";
use Symfony\Component\DomCrawler\Crawler;

$idn = new \idna_convert(array('idn_version' => 2008));
$client = new \GuzzleHttp\Client();

$json = file_get_contents(__DIR__ ."/file.json");
$p = json_decode($json, true);

$new = [];
$all_totals = ["clicks" => 0];
foreach($p as $l) {

    // Get the domain (incl. idna stuff)
    $url = $l["redirect_to"]["url"];
    $domain = parse_url($url)["host"];
    $domain = $idn->encode(utf8_encode($domain));

    // Get the addresses and category from csdb
    $arrCsdb = [
        "category" => "",
        "subcategory" => "",
        "addresses" => []
    ];
    $response = $client->request('GET', 'https://api.cryptoscamdb.org/v1/domain/'. $domain);
    if($response->getStatusCode() === 200) {
        $body = json_decode($response->getBody(), true);
        if(isset($body["success"]) && $body["success"] === true) {
            if(isset($body["result"][0]["addresses"])) {
                if(isset($body["result"][0]["addresses"])) {
                    $arrCsdb["addresses"] = $body["result"][0]["addresses"];
                }

                if(isset($body["result"][0]["category"])) {
                    $arrCsdb["category"] = $body["result"][0]["category"];
                }

                if(isset($body["result"][0]["subcategory"])) {
                    $arrCsdb["subcategory"] = $body["result"][0]["subcategory"];
                }
            }
        }
    }

    // format and get the referrers
    $referrers = [];
    $crawler = new Crawler($l["referrers"]);
    foreach($crawler as $e) {
        $v = $e->nodeValue;
        $v = explode(PHP_EOL, $v);
        // blank
        // <referrer><count>
        // <percentage>
        // blank
        $record = [];
        $v[0] = null;
        foreach($v as $i => $k) {
            $k = trim($k);
            if(trim($k) === "") {
                continue;
            }
            
            if(preg_match("/%$/", $k) == 0) {
                $k = str_replace(",", null, $k);
                preg_match("/(.*?)(\d+)$/", $k, $matches);
                
                if(count($matches)) {
                    $record = [
                        "origin" => trim($matches[1]),
                        "total_clicks" => trim($matches[2]),
                        "percentage" => null
                    ];
                }
            }

            if(preg_match("/%$/", $k)) {
                $record["percentage"] = trim($k);
                $referrers[] = $record;
                $record = [];
            }
        }
    }

    // format and get the locations
    $locations = [];
    $crawler = new Crawler($l["locations"]);
    foreach($crawler as $e) {
        $v = $e->nodeValue;
        $v = explode(PHP_EOL, $v);
        // blank
        // <country><count>
        // <percentage>
        // blank
        $record = [];
        $v[0] = null;
        foreach($v as $i => $k) {
            $k = trim($k);
            if(trim($k) === "") {
                continue;
            }
            
            if(preg_match("/%$/", $k) == 0) {
                $k = str_replace(",", null, $k);
                preg_match("/(.*?)(\d+)$/", $k, $matches);
                
                if(count($matches)) {
                    $record = [
                        "origin" => trim($matches[1]),
                        "total_clicks" => trim($matches[2]),
                        "percentage" => null
                    ];
                }
            }

            if(preg_match("/%$/", $k)) {
                $record["percentage"] = trim($k);
                $locations[] = $record;
                $record = [];
            }
        }
    }

    // format and get the timeline
    $timeline = [];
    $crawler = new Crawler($l["timeline"]);

    $all_totals["clicks"] += str_replace(",", null, $l["clicks"]);

    $new[] = [
        "bitly" => [
            "code" => $l["bitly_code"],
            "link_redirect" => implode("/", ["https://bit.ly", $l["bitly_code"]]),
            "link_analytics" => implode("/", ["https://bit.ly", $l["bitly_code"], "+"]),
            "suspended" => isset($l["bitly_phishing"]) && $l["bitly_phishing"] == 1 ? true : false,
            "created" => [
                "date" => $l["created"]["date"],
                "date_full" => $l["created"]["abs"]
            ],
            "clicks_total" => str_replace(",", null, $l["clicks"]),
            "referrers" => $referrers,
            "locations" => $locations
        ],
        "redirect_to" => [
            "full_path" => $l["redirect_to"]["title"],
            "full_path_url" => $l["redirect_to"]["url"],
            "domain" => $domain
        ],
        "cryptoscamdb" => $arrCsdb
    ];

    // @todo - count to see if the total referrer clicks = clicks_total
    // will need to massage some manually!
}

echo "\r\nTotal Clicks: ". number_format($all_totals["clicks"], 2) ."\r\n";