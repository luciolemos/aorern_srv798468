<?php

namespace App\Helpers;

class MunicipioGeoHelper
{
	private static ?array $catalog = null;

	private static function loadCatalog(): array
	{
		if (self::$catalog !== null) {
			return self::$catalog;
		}

		$path = __DIR__ . '/../Config/municipios_coords.php';
		self::$catalog = file_exists($path) ? include $path : [];
		return self::$catalog;
	}

	public static function get(?int $codigo): ?array
	{
		if (!$codigo) {
			return null;
		}

		$catalog = self::loadCatalog();
		return $catalog[$codigo] ?? null;
	}

	public static function all(): array
	{
		return self::loadCatalog();
	}
}