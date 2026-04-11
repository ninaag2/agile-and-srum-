<?php
// filepath: controllers/HomeController.php

require_once __DIR__ . '/../datafunctions/car_functions.php';

$db = getConnection();
$availableCars = [];
$smallFamilyCars = [];
$premiumCars = [];
$featuredDistricts = [];

$availableCarsResult = getRandomAvailableCars($db, 6);
if (!empty($availableCarsResult['success'])) {
    $availableCars = $availableCarsResult['data'] ?? [];
}

$smallFamilyCarsResult = getRandomSmallFamilyCars($db, 6);
if (!empty($smallFamilyCarsResult['success'])) {
    $smallFamilyCars = $smallFamilyCarsResult['data'] ?? [];
}

$premiumCarsResult = getRandomPremiumCars($db, 6);
if (!empty($premiumCarsResult['success'])) {
    $premiumCars = $premiumCarsResult['data'] ?? [];
}

$featuredDistrictsResult = getFeaturedDistricts($db, 8);
if (!empty($featuredDistrictsResult['success'])) {
    $featuredDistricts = $featuredDistrictsResult['data'] ?? [];
}

require_once __DIR__ . '/../views/pages/home.php';
