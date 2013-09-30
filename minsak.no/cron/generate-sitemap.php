<?php
/*
Copyright 2013 Kommunal- og regionaldepartementet.

This file is part of minsak.no.

Minsak.no is free software: you can redistribute it and/or modify it under the terms of the GNU General Public License version 3 as published by the Free Software Foundation.

Minsak.no is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.

You should have received a copy of the GNU General Public License along with minsak.no. If not, see http://www.gnu.org/licenses/gpl-3.0.html.


*/
?><?php

include('../lib/init.php');
global $app;

$xml = new DOMDocument();
$xml->formatOutput = true;
$root = $xml->appendChild($xml->createElement('urlset'));
$root->appendChild($xml->createAttribute('xmlns'))->appendChild($xml->createTextNode('http://www.sitemaps.org/schemas/sitemap/0.9'));

function addUrl(DOMDocument $xml, DOMElement $root, $url, $priority=null, $changefreq=null, $lastmod=null) {
    $urlNode = $root->appendChild($xml->createElement('url'));
    $urlNode->appendChild($xml->createElement('loc', $url));
    if ($lastmod) {
        $urlNode->appendChild($xml->createElement('lastmod', $lastmod));
    }
    if ($changefreq) {
        $urlNode->appendChild($xml->createElement('changefreq', $changefreq));
    }
    if ($priority) {
        $urlNode->appendChild($xml->createElement('priority', $priority));
    }
    
}

addUrl($xml, $root, BASE_URL, 1, 'daily');
addUrl($xml, $root, BASE_URL . 'om-minsak', 0.9, 'monthly');
addUrl($xml, $root, BASE_URL . 'rediger-sak', 0.4, 'monthly');
addUrl($xml, $root, BASE_URL . 'retningslinjer', 0.9, 'monthly');
addUrl($xml, $root, BASE_URL . 'sok', 0.9, 'daily');
addUrl($xml, $root, BASE_URL . 'fylker-og-kommuner', 0.1, 'yearly');
foreach ($app->dao->getLocations() as $location) {
    addUrl($xml, $root, BASE_URL . $location['slug'], 0.8, 'daily');
}
foreach ($app->dao->getVisibleInitiativesByLocation(9999999) as $initiative) {
    addUrl($xml, $root, BASE_URL . 'sak/' . $initiative['id'], 0.7, 'monthly');
    addUrl($xml, $root, BASE_URL . 'signaturer/' . $initiative['id'], 0.1, 'daily');
}

$xml->save(BASEDIR . '/webroot/sitemap.xml');
