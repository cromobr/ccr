<?php
$date = date("d/m/Y h:i");

// ****** ATEN��O ********
// ABAIXO EST� A CONFIGURA��O DO SEU FORMUL�RIO.
// ****** ATEN��O ********

//CABE�ALHO - ONFIGURA��ES SOBRE SEUS DADOS E SEU WEBSITE
$nome_do_site="CCR MAQ";
$email_para_onde_vai_a_mensagem = "comercial@ccrmaq.com.br";
$nome_de_quem_recebe_a_mensagem = "CCR Maq";
$exibir_apos_enviar='enviado.html';

//MAIS - CONFIGURA�OES DA MENSAGEM ORIGINAL
$cabecalho_da_mensagem_original="From: $name <$email>\n";
$assunto_da_mensagem_original="$nome_do_site - $assunto";

// FORMA COMO RECEBER� O E-MAIL (FORMUL�RIO)
// ******** OBS: SE FOR ADICIONAR NOVOS CAMPOS, ADICIONE OS CAMPOS NA VARI�VEL ABAIXO *************
$configuracao_da_mensagem_original="
Nome: \t$nome
Cidade: \t$cidade
Fone: \t$tel
Email: \t$email
Assunto: \t$assunto
Mensagem: \t$textodamensagem

Enviado em: \t$date
";

if(!$nome) 

die("O campo nome n�o foi preenchido"); 

if(!$email) 

die("O campo email n�o foi preenchido"); 


//CONFIGURA��ES DA MENSAGEM DE RESPOSTA
// CASO $assunto_digitado_pelo_usuario="s" ESSA VARIAVEL RECEBERA AUTOMATICAMENTE A CONFIGURACAO
// "Re: $assunto - $nome_do_site"
$assunto_da_mensagem_de_resposta = "Confirma��o - $nome_do_site";
$cabecalho_da_mensagem_de_resposta = "From: $nome_do_site <$email_para_onde_vai_a_mensagem>\n";
$configuracao_da_mensagem_de_resposta="Obrigado por entrar em contato!\nEstaremos respondendo em breve...\nAtenciosamente,\n$nome_do_site\n\nEnviado em: $date";

// ****** IMPORTANTE ********
// A PARTIR DE AGORA RECOMENDA-SE QUE N�O ALTERE O SCRIPT PARA QUE O SISTEMA FINCIONE CORRETAMENTE
// ****** IMPORTANTE ********

//ESSA VARIAVEL DEFINE SE � O USUARIO QUEM DIGITA O ASSUNTO OU SE DEVE ASSUMIR O ASSUNTO DEFINIDO
//POR VOC� CASO O USUARIO DEFINA O ASSUNTO PONHA "s" NO LUGAR DE "n" E CRIE O CAMPO DE NOME
//'assunto' NO FORMULARIO DE ENVIO
$assunto_digitado_pelo_usuario="n";

//ENVIO DA MENSAGEM ORIGINAL
$headers = "$cabecalho_da_mensagem_original";
if ($assunto_digitado_pelo_usuario=="n")
{
$assunto = "$assunto_da_mensagem_original";
};
$seuemail = "$email_para_onde_vai_a_mensagem";
$mensagem = "$configuracao_da_mensagem_original";
mail($seuemail,$assunto,$mensagem,$headers);

//ENVIO DE MENSAGEM DE RESPOSTA AUTOMATICA
$headers = "$cabecalho_da_mensagem_de_resposta";
if ($assunto_digitado_pelo_usuario=="n")
{
$assunto = "$assunto_da_mensagem_de_resposta";
}
else
{
$assunto = "Re: $assunto - $nome_do_site";
};
$mensagem = "$configuracao_da_mensagem_de_resposta";
mail($email,$assunto,$mensagem,$headers);

echo "<script>window.location='$exibir_apos_enviar'</script>";

?>
