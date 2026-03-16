extends Node
class_name NPCMovimento

@export var pontos: Array[Vector2] = [Vector2(100,0),Vector2(0,0)]
@export var velocidade: float = 100.0
@export var loop: bool = true

var Indice: int = 0
var Personagem_: Personagem;
var Movimento: Callable = Callable()
var Direcao: Vector2 = Vector2.ZERO
var Tempo_Max: int;
var Tempo_Min: int = 0;

func _ready() -> void:
	for i in pontos:
		print(i)

func _physics_process(_delta):
	if not Personagem_ or pontos.is_empty():
		return
	if Tempo_Min > 0:
		Tempo_Min -= _delta;
		return
	var alvo = pontos[Indice]
	var distancia = Personagem_.global_position.distance_to(alvo)
	if distancia < 5.0: 
		Tempo_Min = Tempo_Max;
		Indice += 1;
		if Indice >= pontos.size():
			if loop:
				Indice = 0
			else:
				Indice = pontos.size() - 1;
				Direcao = Vector2.ZERO;
				if Movimento.is_valid():
					Movimento.call(Direcao)
				return
	alvo = pontos[Indice];
	Direcao = Personagem_.global_position.direction_to(alvo)
	
	if Movimento.is_valid():
		Movimento.call(Direcao)
