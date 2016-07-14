# UsefulTableTree
Esta classe é responsável por transformar uma estrutura de dados em níveis
(array( x=>array(y=>array(...)))) em uma tabela HTML com todos os seus rowspans
setados corretamente.

## Particularidade:
A classe funciona para quantos níveis a estrutura de dados possuir, entretanto,
todos os níveis mais distantes da raiz *devem* possuir pelo menos uma folha.

## Atributos da classe
Descrição dos atributos

## Métodos
### __construct
Construtor da classe.

#### Parâmetros
* $data: A estrutura de dados contendo a tabela a ser construída
* $header\_fields: Vetor contendo os nomes das colunas da tabela


### get\_table()
Este método é o único método público da classe. Ele chama os métodos responsáveis
por construir as partes intermediárias da tabela (cabeçalho, corpo e rodapé), concatena
seus retornos e retorna a tabela montada.


### get\_table\_header()
Retorna o cabeçalho da tabela construído de acordo com os nomes das colunas passados
para o construtor e armazenados em $this->header\_fields.


### get\_table\_body()
Este método itera sobre o nível mais alto da estrutura de dados. Cada um dos nós do
primeiro nível é gerado isoladamente, inclusive, com seu próprio <tbody>. A tarefa
de gerar os <tbody> é delegada para a função get\_single\_body().


### get\_single\_body()
Este método é responsável por gerar um <tbody> para uma árvore recebida como parâmetro.
Ele primeiro chama $this->queuefy\_body() que transforma a representação em múltiplos
níveis da estrutura da tabela em uma lista, depois define o nível máximo daquela árvore
recebida e então itera sobre a lista gerada, armazenando o índice atual em $qbody\_index.
Sempre que é encontrado um item do último nível o <tr> é fechado, e concatenado a $single\_tbody,
caso contrário é criado um <td> que tem seu rowspan definido por $this->get\_rowspan(),
que itera sobre $queued\_body a partir de $qbody\_index.

#### Parâmetros
* $data: Uma subárvore com raiz no primeiro nível de $this->data


### queuefy\_body()
Este método implementa uma busca em profundidade recursiva sobre a estrutura
de dados múltinível recebida por parâmetro e gera um array de arrays onde cada
elemento é um array associativo no seguinte formato: ('nível do nó' => 'texto do nó').

#### Parâmetros
* $data: Uma subárvore com raiz no primeiro nível de $this->data
* &$queue: Array passado por referência que será populado durante a busca pela árvore
* $level: Utilizado para manter determinar o nível atual e é utilizado como chave para $queue


### get\_table\_footer()
Método implementado somente para alterações futuras onde seja necessário um footer
customizado, sua função nesta classe atualmente é só fechar a tag </table>, mas poderia
ser utilizado para adicionar informações no rodapé da tabela.


### max\_level()
Este método retorna o nível mais alto da subárvore transformada em lista por $this->queuefy\_body(),
ele existe para dar independência de número de colunas entre as diferentes subárvores da estrutura
multinível.

#### Parâmetros
* $qbody: O array gerado por $this->queuefy\_body()


### get\_rowspan()
Este método retorna o rowspan correto de cada nível, isto é feito contando o número de
elementos do último nível entre $index e a próxima ocorrência do mesmo nível ($level)
em $qbody, ou o final de $qbody.

#### Parâmetros 
* $qbody: O array gerado por $this->queuefy\_body()
* $level: Nível do nó para o qual se está definindo o rowspan
* $index: Posição de $qbody a partir da qual começa o nível que se está definindo o rowspan
