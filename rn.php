<?php
//2020.05.19.09

class RedeNeural{
  private array $Rede = [];
  private int $Bias = 1;
  private int $QtEntradas = 0;
  private int $QtOcultas = 0;
  private int $QtNeuronioOculta = 0;
  private int $QtSaidas = 0;
  private int $IdCamadaSaida;
  private array $Ativacoes = [0 => 'Relu', 1=> 'ReluDx', 2=> 'Sigmoid'];
  private int $AtivacaoOcultas = 0;
  private int $AtivacaoSaida = 0;

  private function FactoryNeuronio(){
    return new class{
      public int $Saida = 1;
      public int $Erro = 0;
      public array $Pesos;
      public array $Erros;
    };
  }

  private function Relu($N):int{
    if($N < 0):
      return 0;
    else:
      return $N;
    endif;
  }

  private function ReluDx($N):int{
    if($N < 0):
      return 0;
    else:
      return 1;
    endif;
  }

  private function Sigmoid($N):float{
    return 1 / (1 + exp(-$N));
  }

  public function __construct(int $QtEntradas, int $QtOcultas, int $QtNeuronioOculta, int $QtSaidas){
    $this->QtEntradas = $QtEntradas;
    $this->QtOcultas = $QtOcultas;
    $this->QtNeuronioOculta = $QtNeuronioOculta;
    $this->QtSaidas = $QtSaidas;
    //Entradas
    for($i = 0; $i < $this->QtEntradas; $i++):
      $this->Rede[0][$i] = $this->FactoryNeuronio();
    endfor;
    //Ocultas
    $count = $this->QtOcultas + 1;
    for($i = 1; $i < $count; $i++):
      for($j = 0; $j < $this->QtNeuronioOculta; $j++):
        $this->Rede[$i][$j] = $this->FactoryNeuronio();
        if($i == 1):
          $qt = $QtEntradas;
        else:
          $qt = $this->QtNeuronioOculta;
        endif;
        for($k = 0; $k < $qt; $k++):
          $this->Rede[$i][$j]->Pesos[$k] = rand(-1000, 1000);
          $this->Rede[$i][$j]->Erros[$k] = 0;
        endfor;
      endfor;
    endfor;
    //Saidas
    $this->IdCamadaSaida = $QtOcultas + 1;
    for($i = 0; $i < $QtSaidas; $i++):
      $this->Rede[$this->IdCamadaSaida][$i] = $this->FactoryNeuronio();
      if($this->QtOcultas == 0):
        $qt = $this->QtEntradas;
      else:
        $qt = $this->QtNeuronioOculta;
      endif;
      for($k = 0; $k < $qt; $k++):
        $this->Rede[$this->IdCamadaSaida][$i]->Pesos[$k] = rand(-1000, 1000);
        $this->Rede[$this->IdCamadaSaida][$i]->Erros[$k] = 0;
      endfor;
    endfor;
  }

  public function AtivacaoOcultasGet():int{
    return $this->AtivacaoOcultas;
  }

  public function AtivacaoOcultasSet(int $N):bool{
    $this->AtivacaoOcultas = $N;
    return true;
  }

  public function AtivacaoSaidaGet():int{
    return $this->AtivacaoSaida;
  }

  public function AtivacaoSaidaSet(int $N):bool{
    $this->AtivacaoSaida = $N;
    return true;
  }

  public function RedeShow():void{
    var_dump($this->Rede);
  }

  public function PesosGet():array{
    $return = [];
    foreach($this->Rede as $IdCamada => $Camada):
      if($IdCamada > 0):
        foreach($Camada as $IdNeuronio => $Neuronio):
          $return[$IdCamada][$IdNeuronio] = $Neuronio->Pesos;
        endforeach;
      endif;
    endforeach;
    return $return;
  }

  public function PesosSet(array $Pesos):bool{
    foreach($Pesos as $IdCamada => $Camada):
      foreach($Camada as $IdNeuronio => $Neuronio):
        $this->Rede[$IdCamada + 1][$IdNeuronio]->Pesos = $Neuronio;
      endforeach;
    endforeach;
    return true;
  }

  public function Calcula():bool{
    foreach(func_get_args() as $id => $arg):
      $this->Rede[0][$id]->Saida = $arg;
    endforeach;
    foreach($this->Rede as $IdCamada => &$Camada):
      if($IdCamada > 0):
        foreach($Camada as &$Neuronio):
          $Neuronio->Saida = 0;
          foreach($Neuronio->Pesos as $IdNeuronio => $Peso):
            $Neuronio->Saida += $this->Rede[$IdCamada - 1][$IdNeuronio]->Saida * $Peso;
          endforeach;
          if($IdCamada == $this->IdCamadaSaida):
            $Neuronio->Saida = $this->Ativacoes[$this->AtivacaoSaida]();
          else:
            $Neuronio->Saida = $this->Ativacoes[$this->AtivacaoOcultas]();
          endif;
        endforeach;
      endif;
    endforeach;
    return true;
  }

  public function Saida($Neuronio):int{
    return $this->Rede[$this->IdCamadaSaida][$Neuronio]->Saida;
  }

  public function CalculaErros(array $Esperado):void{
    for($i = $this->IdCamadaSaida; $i > 0; $i--):
      if($i == $this->IdCamadaSaida):
        foreach($this->Rede[$i] as $IdNeuronio => &$Neuronio):
          $Neuronio->Erros[$IdNeuronio] = $Esperado[$IdNeuronio] - $Neuronio->Saida;
        endforeach;
      else:
        $soma = 0;
        foreach($this->Rede[$i] as $IdNeuronio => &$Neuronio):
          foreach($Neuronio->Pesos as &$Peso):
            $soma += $Peso;
          endforeach;
        endforeach;
        foreach($this->Rede[$i] as $IdNeuronio => &$Neuronio):
          foreach($Neuronio->Pesos as $IdPeso => &$Peso):
            $this->Rede[$i][$IdNeuronio]->Erros[$IdPeso] = $Peso / $soma;
          endforeach;
        endforeach;
      endif;
    endfor;
  }

  public function DesenhaRede():void{
    $ultima = count($this->Rede) - 1;?>
    <style>
      table{
        margin-left: auto;
        margin-right: auto;
      }
      th,td{
        border: solid #000 1px;
        text-align:center;
      }
    </style>
    <table style="width:50%">
      <tr>
        <th>Entradas</th>
        <th colspan="<?php print $ultima - 1;?>">Ocultas</th>
        <th>Saídas</th>
      </tr>
      <tr>  
        <td><?php
          foreach($this->Rede[0] as $Neuronio):?>
            <table>
              <tr><td>Saída: <?php print $Neuronio->Saida;?></td></tr>
            </table><br><?php
          endforeach;?>
        </td>
        <td><?php
          foreach($this->Rede as $IdCamada => $Camada): 
            if($IdCamada > 0 and $IdCamada < $ultima):
              foreach($Camada as $IdNeuronio => $Neuronio):?>
                <table>
                  <tr>
                    <td>Saída: <?php print $Neuronio->Saida;?><br><br><?php
                    foreach($Neuronio->Pesos as $IdPeso => $Peso):?>
                        Ligação <?php print $IdPeso;?>:<br>
                        Peso: <?php print $Peso;?><br>
                        Erro: <?php print $this->Rede[$IdCamada][$IdNeuronio]->Erros[$IdPeso];?><br><br><?php
                    endforeach;?>
                  </tr>
                </table><br><?php
              endforeach;
            endif;
          endforeach;?>
        </td>
        <td><?php
          foreach($this->CamadaSaida as $IdNeuronio => $Neuronio):?>
            <table>
              <tr>
                <td>
                  Saída: <?php print $Neuronio->Saida;?><br><br><?php
                  foreach($Neuronio->Pesos as $IdPeso => $Peso):?>
                      Ligação <?php print $IdPeso;?>:<br>
                      Peso: <?php print $Peso;?><br>
                      Erro: <?php print $Neuronio->Erros[$IdPeso];?><br><br><?php
                  endforeach;?>
                </td>
              </tr>
            </table><br><?php
          endforeach;?>
        </td>
      </tr>
    </table><?php
  }
}