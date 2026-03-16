<?php
class GPXGenerator {
    
    public function gerar($pontos, $opcoes = []) {
        $incluir_descricao = $opcoes['incluir_descricao'] ?? true;
        $tipo_rota = $opcoes['tipo_rota'] ?? 'aberta';
        
        $xml = new DOMDocument('1.0', 'UTF-8');
        $xml->formatOutput = true;
        
        $gpx = $xml->createElement('gpx');
        $gpx->setAttribute('version', '1.1');
        $gpx->setAttribute('creator', 'MapaApp');
        $gpx->setAttribute('xmlns', 'http://www.topografix.com/GPX/1/1');
        
        $metadata = $xml->createElement('metadata');
        $name = $xml->createElement('name', 'Rota MapaApp - ' . date('d/m/Y H:i'));
        $metadata->appendChild($name);
        $gpx->appendChild($metadata);
        
        // Adicionar waypoints
        foreach ($pontos as $ponto) {
            $wpt = $xml->createElement('wpt');
            $wpt->setAttribute('lat', $ponto['Latitude']);
            $wpt->setAttribute('lon', $ponto['Longitude']);
            
            $nome = $xml->createElement('name', htmlspecialchars($ponto['Nome']));
            $wpt->appendChild($nome);
            
            if ($incluir_descricao && !empty($ponto['Descricao'])) {
                $desc = $xml->createElement('desc', htmlspecialchars($ponto['Descricao']));
                $wpt->appendChild($desc);
            }
            
            $tipo = $xml->createElement('type', 'Turístico');
            $wpt->appendChild($tipo);
            
            $gpx->appendChild($wpt);
        }
        
        // Adicionar rota
        if (count($pontos) > 1) {
            $rte = $xml->createElement('rte');
            
            $rteNome = $xml->createElement('name', 'Percurso');
            $rte->appendChild($rteNome);
            
            foreach ($pontos as $ponto) {
                $rtept = $xml->createElement('rtept');
                $rtept->setAttribute('lat', $ponto['Latitude']);
                $rtept->setAttribute('lon', $ponto['Longitude']);
                
                $rteNome = $xml->createElement('name', htmlspecialchars($ponto['Nome']));
                $rtept->appendChild($rteNome);
                
                $rte->appendChild($rtept);
            }
            
            $gpx->appendChild($rte);
        }
        
        $xml->appendChild($gpx);
        return $xml->saveXML();
    }
}