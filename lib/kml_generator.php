<?php
class KMLGenerator {
    
    public function gerar($pontos, $opcoes = []) {
        $incluir_descricao = $opcoes['incluir_descricao'] ?? true;
        $incluir_info = $opcoes['incluir_info'] ?? true;
        $incluir_linha = $opcoes['incluir_linha'] ?? true;
        $tipo_rota = $opcoes['tipo_rota'] ?? 'aberta';
        
        $xml = new DOMDocument('1.0', 'UTF-8');
        $xml->formatOutput = true;
        
        $kml = $xml->createElement('kml');
        $kml->setAttribute('xmlns', 'http://www.opengis.net/kml/2.2');
        $document = $xml->createElement('Document');
        
        // Nome da rota
        $nome = $xml->createElement('name', 'Rota MapaApp - ' . date('d/m/Y H:i'));
        $document->appendChild($nome);
        
        // Descrição
        $desc = $xml->createElement('description', 
            'Rota com ' . count($pontos) . ' pontos turísticos gerada pelo MapaApp');
        $document->appendChild($desc);
        
        // Estilo para marcadores
        $style = $xml->createElement('Style');
        $style->setAttribute('id', 'ponto-estilo');
        
        $iconStyle = $xml->createElement('IconStyle');
        $icon = $xml->createElement('Icon');
        $href = $xml->createElement('href', 'http://maps.google.com/mapfiles/kml/paddle/grn-blank.png');
        $icon->appendChild($href);
        $iconStyle->appendChild($icon);
        $style->appendChild($iconStyle);
        
        $labelStyle = $xml->createElement('LabelStyle');
        $scale = $xml->createElement('scale', '0.8');
        $labelStyle->appendChild($scale);
        $style->appendChild($labelStyle);
        
        $document->appendChild($style);
        
        // Adicionar cada ponto
        $coordenadas_linha = [];
        
        foreach ($pontos as $ponto) {
            $placemark = $xml->createElement('Placemark');
            
            // Nome
            $nomePlace = $xml->createElement('name', htmlspecialchars($ponto['Nome']));
            $placemark->appendChild($nomePlace);
            
            // Descrição
            if ($incluir_descricao) {
                $descricao = $xml->createElement('description');
                $descricaoTexto = '';
                
                if (!empty($ponto['Descricao'])) {
                    $descricaoTexto .= '<p><b>Descrição:</b> ' . htmlspecialchars($ponto['Descricao']) . '</p>';
                }
                
                if ($incluir_info) {
                    $descricaoTexto .= '<p><b>Categoria:</b> ' . htmlspecialchars($ponto['categoria']) . '</p>';
                    if (!empty($ponto['Morada'])) {
                        $descricaoTexto .= '<p><b>Morada:</b> ' . htmlspecialchars($ponto['Morada']) . '</p>';
                    }
                    if (!empty($ponto['Cidade']) || !empty($ponto['Pais'])) {
                        $descricaoTexto .= '<p><b>Localização:</b> ' . 
                            htmlspecialchars($ponto['Cidade'] . ($ponto['Cidade'] && $ponto['Pais'] ? ', ' : '') . $ponto['Pais']) . '</p>';
                    }
                    if (!empty($ponto['Telefone'])) {
                        $descricaoTexto .= '<p><b>Telefone:</b> ' . htmlspecialchars($ponto['Telefone']) . '</p>';
                    }
                    if (!empty($ponto['Email'])) {
                        $descricaoTexto .= '<p><b>Email:</b> ' . htmlspecialchars($ponto['Email']) . '</p>';
                    }
                    if (!empty($ponto['Website'])) {
                        $descricaoTexto .= '<p><b>Website:</b> <a href="' . htmlspecialchars($ponto['Website']) . '">' . 
                            htmlspecialchars($ponto['Website']) . '</a></p>';
                    }
                }
                
                $descricao->appendChild($xml->createCDATASection($descricaoTexto));
                $placemark->appendChild($descricao);
            }
            
            // Estilo
            $styleUrl = $xml->createElement('styleUrl', '#ponto-estilo');
            $placemark->appendChild($styleUrl);
            
            // Ponto
            $point = $xml->createElement('Point');
            $coords = $xml->createElement('coordinates', 
                $ponto['Longitude'] . ',' . $ponto['Latitude'] . ',0');
            $point->appendChild($coords);
            $placemark->appendChild($point);
            
            $document->appendChild($placemark);
            
            // Guardar coordenadas para linha
            $coordenadas_linha[] = $ponto['Longitude'] . ',' . $ponto['Latitude'] . ',0';
        }
        
        // Adicionar linha da rota
        if ($incluir_linha && count($coordenadas_linha) > 1) {
            $placemark = $xml->createElement('Placemark');
            
            $nomeLinha = $xml->createElement('name', 'Percurso');
            $placemark->appendChild($nomeLinha);
            
            $descLinha = $xml->createElement('description', 'Linha do percurso');
            $placemark->appendChild($descLinha);
            
            // Estilo da linha
            $styleLinha = $xml->createElement('Style');
            $lineStyle = $xml->createElement('LineStyle');
            $color = $xml->createElement('color', 'ff00ff00'); // Verde
            $width = $xml->createElement('width', '4');
            $lineStyle->appendChild($color);
            $lineStyle->appendChild($width);
            $styleLinha->appendChild($lineStyle);
            $placemark->appendChild($styleLinha);
            
            $lineString = $xml->createElement('LineString');
            
            $altitudeMode = $xml->createElement('altitudeMode', 'clampToGround');
            $lineString->appendChild($altitudeMode);
            
            $coordsLinha = $xml->createElement('coordinates', implode("\n", $coordenadas_linha));
            $lineString->appendChild($coordsLinha);
            
            $placemark->appendChild($lineString);
            $document->appendChild($placemark);
        }
        
        $kml->appendChild($document);
        $xml->appendChild($kml);
        
        return $xml->saveXML();
    }
}