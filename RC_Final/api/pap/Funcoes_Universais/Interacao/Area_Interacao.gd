class_name Area_Interacao;
extends Area2D

enum Tipo_Interacao {COMERCIO, DIALOGO, ABRIR, JOGADOR}
var Interacao 

func _ready() -> void:
	area_entered.connect(entrou);

func entrou():
	var Interacao = preload("res://Funcoes_Universais/Interacao/balao_interacao.tscn");
	get_parent().add_child(Interacao);
	
