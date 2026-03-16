<?php
class RouteOptimizer {
    
    public function otimizar($pontos, $circular = false) {
        if (count($pontos) < 3) {
            return $pontos;
        }
        
        $otimizados = [];
        $restantes = $pontos;
        
        // Começar pelo primeiro ponto
        $atual = array_shift($restantes);
        $otimizados[] = $atual;
        
        while (!empty($restantes)) {
            $indiceMaisProximo = 0;
            $distanciaMinima = PHP_FLOAT_MAX;
            
            foreach ($restantes as $i => $ponto) {
                $dist = $this->calcularDistancia(
                    $atual['Latitude'], $atual['Longitude'],
                    $ponto['Latitude'], $ponto['Longitude']
                );
                
                if ($dist < $distanciaMinima) {
                    $distanciaMinima = $dist;
                    $indiceMaisProximo = $i;
                }
            }
            
            $atual = $restantes[$indiceMaisProximo];
            $otimizados[] = $atual;
            array_splice($restantes, $indiceMaisProximo, 1);
        }
        
        // Se for circular, voltar ao início
        if ($circular) {
            $otimizados[] = $otimizados[0];
        }
        
        return $otimizados;
    }
    
    private function calcularDistancia($lat1, $lon1, $lat2, $lon2) {
        // Fórmula de Haversine
        $R = 6371; // Raio da Terra em km
        
        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);
        
        $a = sin($dLat/2) * sin($dLat/2) +
             cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
             sin($dLon/2) * sin($dLon/2);
        
        $c = 2 * atan2(sqrt($a), sqrt(1-$a));
        
        return $R * $c;
    }
}