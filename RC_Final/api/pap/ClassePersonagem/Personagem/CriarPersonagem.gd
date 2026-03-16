class_name Criar_Personagem
extends Marker2D

enum Especie { Jogador, HUMANO, GOBLIN, ELFO, ORC }
enum Classe { GUERREIRO, MAGO, ARQUEIRO, CURANDEIRO }
enum Agressividade { PASSIVO, NEUTRO, HOSTIL }
enum Tipo_Interacao {COMERCIO, DIALOGO, ABRIR, JOGADOR}

@export_group("Identidade")
@export var nome: String = ""
@export var jogador: bool = false
@export var especie: Especie = Especie.HUMANO
@export var classe: Classe = Classe.GUERREIRO
@export var idade: int = 25

@export_group("Atributos")
@export var nivel: int = 1
@export var experiencia: int = 0
@export var inteligencia: int = 1
@export var forca: int = 1
@export var velocidade: int = 1
@export var defesa: int = 1
@export var sorte: int = 1
@export var e_pontos: int = 0
@export var h_pontos: int = 0

@export_group("Combate")
@export var participa_combate: bool = true
@export var habilidades: Array[String] = []
@export var pode_ressurgir: bool = false
@export var tempo_ressurgimento: float = 30.0
@export var drops: Array[String] = []
@export var experiencia_ao_matar: int = 0

@export_group("Equipamento")
@export_subgroup("Armas")
@export var arma_equipada: String = ""
@export var arma_secundaria: String = ""

@export_subgroup("Armadura")
@export var capacete: String = ""
@export var peitoral: String = ""
@export var calca: String = ""
@export var botas: String = ""
@export var luvas: String = ""

@export_subgroup("Acessórios")
@export var colar: String = ""
@export var pulseira: String = ""
@export var anel_1: String = ""
@export var anel_2: String = ""
@export var anel_3: String = ""

@export_group("PNJ")
@export var esta_ativo: bool = true
@export var agressividade: Agressividade = Agressividade.NEUTRO
@export var distancia_deteccao: float = 200.0
@export var pode_chamar_aliados: bool = false
@export var faction: String = ""
@export var missao_relacionada: String = ""
@export var condicao_aparecimento: String = ""
@export var movimentacao: bool = false
@export_enum("Circulo", "Linha", "Triangulo_Pe", "Triangulo_Ba",  "Quadrado") var tipo_caminho: String = "Circulo"
@export_range(100, 1000, 1, "suffix:px") var distancia_entre_pontos: float = 500
@export_range(0, 20, 1) var vertices: int = 41
@export_range(0, 60, 1, "suffix:t") var Tempo_Parado : float = 10;

@export_group("Recrutamento")
@export var pode_ser_recrutado: bool = false
@export var esta_recrutado: bool = false
@export var custo_recrutamento: int = 0
@export var condicao_recrutamento: String = ""

@export_group("Dialogo")
@export var tem_dialogo: bool = false
@export var dialogos: String = ""
@export var e_quest_giver: bool = false
@export var quests_disponiveis: Array[String] = []

@export_group("Comercio")
@export var Tem_Comercio: bool = false
@export var inventario: Array[String] = []
@export var moedas: int = 0


@export_group("Puzzle")
@export var pode_empurrar: bool = false
@export var pode_ativar_mecanismos: bool = true
@export var chaves_iniciais: int = 0

@export_group("Funções Especiais")

@export var funcao_especial: String = ""
@export var funcao_especial_gatilho: String = "ao_morrer"

@export var funcao_especial_parametros: Array[String] = []

@export_group("Tags")

@export var tags: Array[String] = []

@export_group("Som")
@export var som_ao_morrer: AudioStream
@export var som_ao_atacar: AudioStream
@export var som_ao_detectar_jogador: AudioStream


func _validate_property(property: Dictionary) -> void:
	var apenas_pnj := [
		"esta_ativo", "agressividade", "distancia_deteccao",
		"pode_chamar_aliados", "faction", "missao_relacionada",
		"condicao_aparecimento", "movimentacao",
		"tem_dialogo", "e_quest_giver",
		"e_comerciante", "pode_ser_recrutado",
		"pode_ressurgir", "drops", "experiencia_ao_matar",
		"som_ao_detectar_jogador",
		"funcao_especial", "funcao_especial_gatilho",
		"funcao_especial_parametros"
	]
	
	if jogador:
		if property.name in apenas_pnj:
			property.usage = PROPERTY_USAGE_NO_EDITOR
			return
		match property.name:
			"tipo_caminho", "distancia_entre_pontos", "vertices", "dialogos", "quests_disponiveis",\
			"inventario", "moedas", "tempo_ressurgimento", "esta_recrutado", "custo_recrutamento",\
			"condicao_recrutamento":
				property.usage = PROPERTY_USAGE_NO_EDITOR
				return
	else:
		match property.name:
			"tipo_caminho", "distancia_entre_pontos", "vertices":
				if not movimentacao:
					property.usage = PROPERTY_USAGE_NO_EDITOR
					return
	
		if property.name == "dialogos" and not tem_dialogo:
			property.usage = PROPERTY_USAGE_NO_EDITOR
			return
	
		if property.name == "quests_disponiveis" and not e_quest_giver:
			property.usage = PROPERTY_USAGE_NO_EDITOR
			return
	
		match property.name:
			"inventario", "moedas":
				if not Tem_Comercio:
					property.usage = PROPERTY_USAGE_NO_EDITOR
					return
	
		if property.name == "tempo_ressurgimento" and not pode_ressurgir:
			property.usage = PROPERTY_USAGE_NO_EDITOR
			return
	
		match property.name:
			"esta_recrutado", "custo_recrutamento", "condicao_recrutamento":
				if not pode_ser_recrutado:
					property.usage = PROPERTY_USAGE_NO_EDITOR
					return
	
	if property.name == "habilidades" and not participa_combate:
		property.usage = PROPERTY_USAGE_NO_EDITOR

func _ready() -> void:
	var Tipo : String;
	if jogador:
		Tipo = "Jogador";
	else:
		Tipo = "NPC";
	var Caminho_Animacao = "res://ClassePersonagem/Animacoes/"+Tipo+".tscn";
	var Caminho_Movimento = "res://ClassePersonagem/"+Tipo+"/Movimento/"+Tipo+"Movimento.tscn";
	var Caminho_Personagem = "res://ClassePersonagem/Personagem/Personagem.tscn";
	var Caminho_Area = "res://Funcoes_Universais/Interacao/Area_Interacao.tscn";
	
	if not ResourceLoader.exists(Caminho_Animacao):
		push_error("Animação não encontrada: " + Caminho_Animacao)
		return
	if not ResourceLoader.exists(Caminho_Personagem):
		push_error("Personagem não encontrado: " + Caminho_Personagem)
		return
	if not ResourceLoader.exists(Caminho_Movimento):
		push_error("Movimento não encontrado: " + Caminho_Movimento)
		return
	var Personagem_: Personagem = load(Caminho_Personagem).instantiate()
	var Animacao = load(Caminho_Animacao).instantiate()
	var Movimento = load(Caminho_Movimento).instantiate()
	if ResourceLoader.exists(Caminho_Area):
		var Area_Interacao_ = load(Caminho_Area).instantiate()
		Area_Interacao_.Interacao = Tipo_Interacao
		Personagem_.add_child(Area_Interacao_)
	
	Animacao.name = "Anim";
	if Movimento.has_method("set_Movimento") or "Movimento" in Movimento:
		Movimento.Movimento = Personagem_.Movimento
		if not jogador:
			Movimento.Personagem_ = Personagem_ as Personagem
			Movimento.Tempo_Max = Tempo_Parado*60;
			Movimento.pontos = NPCMovimentacao.get_pontos(tipo_caminho, distancia_entre_pontos, vertices);
			Personagem_.global_position = Movimento.pontos[0];
		else:
			Personagem_.global_position = global_position
	
	Personagem_.add_child(Animacao)
	Personagem_.add_child(Movimento)
	get_tree().current_scene.add_child.call_deferred(Personagem_)
	queue_free()
