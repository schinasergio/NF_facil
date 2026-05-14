<?php

namespace App\Services\Fiscal;

class TaxCalculator
{
    /**
     * Calcula os tributos (ICMS, PIS, COFINS) simplificados para o MVP.
     * 
     * @param array $item Dados do produto/item
     * @param int $regimeTributario 1=Simples Nacional, 3=Regime Normal
     * @return array Array com os valores calculados
     */
    public static function calculate(array $item, int $regimeTributario): array
    {
        $preco = $item['preco_venda'] ?? 0;
        $quantidade = 1;
        $vProd = $preco * $quantidade;

        $taxes = [
            'vProd' => $vProd,
            'ICMS' => [],
            'PIS' => [],
            'COFINS' => [],
            'vTotTrib' => 0 // Valor aproximado de tributos (Lei da Transparencia)
        ];

        if ($regimeTributario == 1) {
            // Simples Nacional (CSOSN)
            // Exemplo CSOSN 102: Tributada pelo Simples Nacional sem permissao de credito
            $taxes['ICMS'] = [
                'orig' => $item['origem'] ?? 0,
                'CSOSN' => '102'
            ];
            $taxes['PIS'] = ['CST' => '49']; // Outras operacoes
            $taxes['COFINS'] = ['CST' => '49'];
        } else {
            // Regime Normal (CST)
            // Exemplo CST 00: Tributada integralmente
            $pICMS = 18.00;
            $vICMS = $vProd * ($pICMS / 100);

            $taxes['ICMS'] = [
                'orig' => $item['origem'] ?? 0,
                'CST' => '00',
                'modBC' => 3, // Valor da Operacao
                'vBC' => $vProd,
                'pICMS' => $pICMS,
                'vICMS' => $vICMS
            ];

            // PIS (CST 01)
            $pPIS = 1.65;
            $vPIS = $vProd * ($pPIS / 100);
            $taxes['PIS'] = [
                'CST' => '01',
                'vBC' => $vProd,
                'pPIS' => $pPIS,
                'vPIS' => $vPIS
            ];

            // COFINS (CST 01)
            $pCOFINS = 7.60;
            $vCOFINS = $vProd * ($pCOFINS / 100);
            $taxes['COFINS'] = [
                'CST' => '01',
                'vBC' => $vProd,
                'pCOFINS' => $pCOFINS,
                'vCOFINS' => $vCOFINS
            ];
        }

        // Simulação IBPT (Aproximado)
        $taxes['vTotTrib'] = $vProd * 0.30; // 30% mock para IBPT

        return $taxes;
    }
}
