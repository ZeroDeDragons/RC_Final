class_name JogadorMovimento
extends Node2D

var Movimento: Callable = Callable()

func _process(_delta: float) -> void:
	if Movimento.is_valid():
		var direcao = Input.get_vector("Esquerda", "Direita", "Cima", "Baixo")
		Movimento.call(direcao);
