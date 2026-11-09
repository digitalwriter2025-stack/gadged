<?php

namespace App\Enums;

/**
 * A slot in the PC builder. Each slot picks products from the first catalog
 * category whose slug exists among categorySlugs(), including its subcategories.
 */
enum PcBuilderComponent: string
{
    case Cpu = 'cpu';
    case CpuCooler = 'cpu-cooler';
    case Motherboard = 'motherboard';
    case Ram = 'ram';
    case Storage = 'storage';
    case GraphicsCard = 'graphics-card';
    case PowerSupply = 'power-supply';
    case Casing = 'casing';
    case Monitor = 'monitor';
    case CasingCooler = 'casing-cooler';
    case Keyboard = 'keyboard';
    case Mouse = 'mouse';
    case Speaker = 'speaker';
    case Headphone = 'headphone';
    case NetworkAdapter = 'wifi-adapter';
    case AntiVirus = 'anti-virus';
    case Ups = 'ups';

    /**
     * In the storefront language; the admin panel shows English.
     */
    public function label(): string
    {
        return __(match ($this) {
            self::Cpu => 'CPU',
            self::CpuCooler => 'CPU Cooler',
            self::Motherboard => 'Motherboard',
            self::Ram => 'RAM',
            self::Storage => 'Storage',
            self::GraphicsCard => 'Graphics Card',
            self::PowerSupply => 'Power Supply',
            self::Casing => 'Casing',
            self::Monitor => 'Monitor',
            self::CasingCooler => 'Casing Cooler',
            self::Keyboard => 'Keyboard',
            self::Mouse => 'Mouse',
            self::Speaker => 'Speaker & Home Theater',
            self::Headphone => 'Headphone',
            self::NetworkAdapter => 'Wifi Adapter / LAN Card',
            self::AntiVirus => 'Anti Virus',
            self::Ups => 'UPS',
        });
    }

    public function isRequired(): bool
    {
        return in_array($this, [self::Cpu, self::Motherboard, self::Ram, self::Storage], true);
    }

    public function isCore(): bool
    {
        return in_array($this, [
            self::Cpu, self::CpuCooler, self::Motherboard, self::Ram,
            self::Storage, self::GraphicsCard, self::PowerSupply, self::Casing,
        ], true);
    }

    /**
     * Candidate category slugs, most specific first.
     *
     * @return list<string>
     */
    public function categorySlugs(): array
    {
        return match ($this) {
            self::Cpu => ['processor', 'cpu'],
            self::CpuCooler => ['cpu-cooler'],
            self::Motherboard => ['motherboard'],
            self::Ram => ['ram', 'desktop-ram'],
            self::Storage => ['ssd', 'hdd', 'storage'],
            self::GraphicsCard => ['graphics-card'],
            self::PowerSupply => ['power-supply'],
            self::Casing => ['casing'],
            self::Monitor => ['monitor'],
            self::CasingCooler => ['casing-cooler'],
            self::Keyboard => ['keyboard'],
            self::Mouse => ['mouse'],
            self::Speaker => ['speaker'],
            self::Headphone => ['headphone'],
            self::NetworkAdapter => ['wifi-adapter', 'lan-card', 'network-adapter'],
            self::AntiVirus => ['anti-virus', 'antivirus'],
            self::Ups => ['ups'],
        };
    }

    /**
     * Typical draw in watts, used for the rough "Estimated Wattage" figure.
     */
    public function estimatedWattage(): int
    {
        return match ($this) {
            self::Cpu => 95,
            self::CpuCooler => 5,
            self::Motherboard => 50,
            self::Ram => 10,
            self::Storage => 8,
            self::GraphicsCard => 220,
            self::CasingCooler => 10,
            self::Keyboard, self::Mouse => 3,
            default => 0,
        };
    }

    public function icon(): string
    {
        return match ($this) {
            self::Cpu, self::NetworkAdapter => 'cpu',
            self::CpuCooler, self::CasingCooler => 'fan',
            self::Motherboard => 'board',
            self::Ram => 'memory',
            self::Storage => 'drive',
            self::GraphicsCard => 'gpu',
            self::PowerSupply, self::Ups => 'plug',
            self::Casing => 'tower',
            self::Monitor => 'monitor',
            self::Keyboard => 'keyboard',
            self::Mouse => 'mouse',
            self::Speaker => 'speaker',
            self::Headphone => 'headphone',
            self::AntiVirus => 'shield',
        };
    }
}
