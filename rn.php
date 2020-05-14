<?php
//2020.05.14.03

class RedeNeural{
  private array $Rede = [];
  private int $Bias = 1;
  private int $QtEntradas = 0;
  private int $QtOcultas = 0;
  private int $QtNeuronioOculta = 0;
  private int $QtSaidas = 0;
  private array $CamadaSaida;

  public function __construct(int $QtEntradas, int $QtOcultas, int $QtNeuronioOculta, int $QtSaidas){
    $this->QtEntradas = $QtEntradas;
    $this->QtOcultas = $QtOcultas;
    $this->QtNeuronioOculta = $QtNeuronioOculta;
    $this->QtSaidas = $QtSaidas;
    //Entradas
    for($i = 0; $i < $this->QtEntradas; $i++):
      $this->Rede[0][$i]['Saida'] = 1;
    endfor;
    //Ocultas
    $count = $this->QtOcultas + 1;
    for($i = 1; $i < $count; $i++):
      for($j = 0; $j < $this->QtNeuronioOculta; $j++):
        $this->Rede[$i][$j]['Saida'] = 1;
        if($i == 1):
          $qt = $QtEntradas;
        else:
          $qt = $this->QtNeuronioOculta;
        endif;
        for($k = 0; $k < $qt; $k++):
          $this->Rede[$i][$j]['Erro'] = 0;
          $this->Rede[$i][$j]['Pesos'][$k] = rand(-1000, 1000);
          $this->Rede[$i][$j]['Erros'][$k] = 0;
        endfor;
      endfor;
    endfor;
    //Saidas
    $CamadaSaida = $QtOcultas + 1;
    for($i = 0; $i < $QtSaidas; $i++):
      $this->Rede[$CamadaSaida][$i]['Saida'] = 1;
      if($this->QtOcultas == 0):
        $qt = $this->QtEntradas;
      else:
        $qt = $this->QtNeuronioOculta;
      endif;
      for($k = 0; $k < $qt; $k++):
        $this->Rede[$CamadaSaida][$i]['Pesos'][$k] = rand(-1000, 1000);
        $this->Rede[$CamadaSaida][$i]['Erros'][$k] = 0;
      endfor;
    endfor;
    $this->CamadaSaida = &$this->Rede[$CamadaSaida];
  }

  public function Entrada():bool{
    foreach(func_get_args() as $id => $arg):
      $this->Rede[0][$id]['Saida'] = $arg;
    endforeach;
    return true;
  }

  public function Saida($Id):int{
    return $this->CamadaSaida[$Id]['Saida'];
  }

  public function PesosSet(array $Rede):bool{
    foreach($Rede as $Id1 => $Camada):
      foreach($Camada as $Id2 => $Peso):
        $this->Rede[$Id1 + 1][$Id2]['Pesos'] = $Peso;
      endforeach;
    endforeach;
    return true;
  }

  public function PesosGet():array{
    $return = [];
    foreach($this->Rede as $Id1 => $Camada):
      foreach($Camada as $Id2 => $Peso):
        if($Id1 > 0 and $Id1 <= $this->QtOcultas):
          $return[$Id1][$Id2] = $this->Rede[$Id1][$Id2]['Pesos'];
        endif;
      endforeach;
    endforeach;
    return $return;
  }

  public function Calcula():void{
    foreach($this->Rede as $IdCamada => &$Camada):
      if($IdCamada > 0):
        foreach($Camada as &$Neuronio):
          $soma = 0;
          foreach($Neuronio['Pesos'] as $IdNeuronio => $Peso):
            $soma += $this->Rede[$IdCamada - 1][$IdNeuronio]['Saida'] * $Peso;
          endforeach;
          $Neuronio['Saida'] = $soma;
        endforeach;
      endif;
    endforeach;
  }

  public function RedeGet():array{
    return $this->Rede;
  }

  public function CalculaErros(array $Esperado):void{
    $ultima = count($this->Rede) - 1;
    for($i = $ultima; $i > 0; $i--):
      if($i == $ultima):
        foreach($this->Rede[$i] as $IdNeuronio => &$Neuronio):
          $Neuronio['Erro'] = $Esperado[$IdNeuronio] - $Neuronio['Saida'];
        endforeach;
      else:
        $soma = 0;
        foreach($this->Rede[$i] as $IdNeuronio => &$Neuronio):
          foreach($Neuronio['Pesos'] as &$Peso):
            $soma += $Peso;
          endforeach;
        endforeach;
        foreach($this->Rede[$i] as $IdNeuronio => &$Neuronio):
          foreach($Neuronio['Pesos'] as $IdPeso => &$Peso):
            $this->Rede[$i][$IdNeuronio]['Erros'][$IdPeso] = $Peso / $soma;
          endforeach;
        endforeach;
      endif;
    endfor;
  }
}