<?php
ini_set("display_errors", 1);
ini_set("display_startup_errors", 1);
ini_set("error_reporting", -1);

class RedeNeural{
  private array $Rede = [];
  private int $Bias = 1;
  private int $QtEntradas = 0;
  private int $QtOcultas = 0;
  private int $QtNeuronioOculta = 0;
  private int $QtSaidas = 0;
  private int $IdSaida = 0;

  public function __construct(int $QtEntradas, int $QtOcultas, int $QtNeuronioOculta, int $QtSaidas){
    $this->QtEntradas = $QtEntradas;
    $this->QtOcultas = $QtOcultas;
    $this->QtNeuronioOculta = $QtNeuronioOculta;
    $this->QtSaidas = $QtSaidas;
    $this->IdSaida = $this->QtOcultas + 1;
    //Entradas
    for($i = 0; $i < $this->QtEntradas; $i++)
      $this->Rede[0][$i]["Saida"] = 1;
    //Ocultas
    $count = $this->QtOcultas + 1;
    for($i = 1; $i < $count; $i++):
      for($j = 0; $j < $this->QtNeuronioOculta; $j++):
        $this->Rede[$i][$j]["Saida"] = 1;
        if($i == 1):
          for($k = 0; $k < $QtEntradas; $k++)
            $this->Rede[$i][$j]["Pesos"][$k] = rand(-1000, 1000);
        else:
          for($k = 0; $k < $this->QtNeuronioOculta; $k++)
            $this->Rede[$i][$j]["Pesos"][$k] = rand(-1000, 1000);
        endif;
      endfor;
    endfor;
    //Saidas
    $Saida = $QtOcultas + 1;
    for($i = 0; $i < $QtSaidas; $i++)
      $this->Rede[$Saida][$i]["Saida"] = 1;
  }

  public function Entrada():bool{
    foreach(func_get_args() as $id => $arg)
      $this->Rede[0][$id]["Saida"] = $arg;
    return true;
  }

  public function PesosSet(array $Rede):bool{
    foreach($Rede as $Id1 => $Camada)
      foreach($Camada as $Id2 => $Peso)
        $this->Rede[$Id1 + 1][$Id2]["Pesos"] = $Peso;
    return true;
  }

  public function PesosGet():array{
    $return = [];
    foreach($this->Rede as $Id1 => $Camada)
      foreach($Camada as $Id2 => $Peso)
        if($Id1 > 0 and $Id1 < $this->IdSaida)
          $return[$Id1][$Id2] = $this->Rede[$Id1][$Id2]["Pesos"];
    return $return;
  }

  public function Calcula():array{
    foreach($this->Rede as $IdCamada => &$Camada)
      if($IdCamada > 0 and $IdCamada < $this->IdSaida):
        foreach($Camada as &$Neuronio):
          $soma = 0;
          foreach($Neuronio["Pesos"] as $IdNeuronio => $Peso)
            $soma += $this->Rede[$IdCamada - 1][$IdNeuronio]["Saida"] * $Peso;
          if($soma < 0)
            $soma = 0;
          $Neuronio["Saida"] = $soma;
        endforeach;
      elseif($IdCamada == $this->IdSaida):
        $anterior = $IdCamada - 1;
        foreach($Camada as $IdNeuronio => &$Saida)
          $Saida["Saida"] = $this->Rede[$anterior][$IdNeuronio]["Saida"] > 0? 1: 0;
      endif;
    $return = [];
    foreach($this->Rede[$this->IdSaida] as &$Saida)
      $return[] = $Saida["Saida"];
    return $return;
  }

  public function RedeGet():array{
    return $this->Rede;
  }
}

$teste = new RedeNeural(3,2,2,2);
$teste->PesosSet([
  [
    [100,1,5],
    [-200,5,10]
  ],
  [
    [1,500],
    [0,-500]
  ]
]);
for($i = 500; $i > 0; $i--):
  $teste->Entrada($i,15,8);
  $a = $teste->Calcula();
  echo $i . " - " . $a[0] . " - " . $a[1] . "<br>";
endfor;