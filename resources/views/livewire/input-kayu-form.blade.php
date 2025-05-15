class InputKayuForm extends Component
{
    public $jenisKayu = 'Sengon';

    public $panjang = [100, 130, 200, 260];
    public $grades = [
        'Rijek' => [10, 11, 12, 13, 14, 15, 16, 17, 18, 19, 20, 21, 22, 23, 24, 25, 26, 27, 28, 29, 30],
        'Story' => [13, 14, 15, 16, 17, 18, 26, 27, 28, 29, 30],
        'Super' => [19, 20, 21, 22, 23, 24, 25],
    ];

    public $pcs = [];

    public function mount()
    {
        foreach ($this->grades as $grade => $diameters) {
            foreach ($diameters as $d) {
                foreach ($this->panjang as $p) {
                    $this->pcs[$grade][$d][$p] = 0;
                }
            }
        }
    }

    public function render()
    {
        return view('livewire.input-kayu-form');
    }

    public function totalPcs($grade, $diameter)
    {
        return array_sum($this->pcs[$grade][$diameter]);
    }

    public function totalVolume()
    {
        $volume = 0;
        foreach ($this->grades as $grade => $diameters) {
            foreach ($diameters as $d) {
                foreach ($this->panjang as $p) {
                    $r = $d / 100 / 2; // radius in meters
                    $vol = pi() * $r * $r * ($p / 100); // m3 per batang
                    $volume += $vol * $this->pcs[$grade][$d][$p];
                }
            }
        }
        return round($volume, 3);
    }

    public function totalBayar($hargaPerM3 = 1000000)
    {
        return $this->totalVolume() * $hargaPerM3;
    }
}
