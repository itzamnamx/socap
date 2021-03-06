<?php
//=====================================================================================================
//=====>	INICIO_H
	include_once("../core/go.login.inc.php");
	include_once("../core/core.error.inc.php");
	include_once("../core/core.html.inc.php");
	include_once("../core/core.init.inc.php");
	$theFile					= __FILE__;
	$permiso					= getSIPAKALPermissions($theFile);
	if($permiso === false){		header ("location:../404.php?i=999");	}
	$_SESSION["current_file"]	= addslashes( $theFile );
//<=====	FIN_H
	$iduser = $_SESSION["log_id"];
//=====================================================================================================
$xHP		= new cHPage("TR.Panel de Control de personas", HP_FORM);
$jxc 		= new TinyAjax();
$xLi		= new cSQLListas();
$ql			= new MQL();
$xImg		= new cHImg();
$xF			= new cFecha();
$xRuls		= new cReglaDeNegocio();
$xODT		= new cHDicccionarioDeTablas();
$xLog		= new cCoreLog();

$jsTabs		= "";
$idempresa	= 0;
$oficial 	= elusuario($iduser);
$idsocio 	= parametro("idsocio", false, MQL_INT); $idsocio 	= parametro("persona", $idsocio, MQL_INT); $idsocio 	= parametro("socio", $idsocio, MQL_INT);
$xJsB		= new jsBasicForm("extrasocios");

function jsaReVivienda($idsocio){
	$xLi		= new cSQLListas();
	
			
		$cTbl = new cTabla($xLi->getListadoDeDireccionesPorPer($idsocio));
		$cTbl->OButton("TR.Verificar", "jsVerificar(_REPLACE_ID_)", $cTbl->ODicIcons()->SALUD);
		$cTbl->addEditar(USUARIO_TIPO_OFICIAL_CRED);
		$cTbl->addEliminar(USUARIO_TIPO_OFICIAL_CRED);
		$cTbl->setKeyField("idsocios_vivienda");
		$cTbl->setEventKey("var xPv=new PersVivGen();xPv.getVerVivienda");
			
		return $cTbl->Show();
}
function jsaReActividadE($idsocio){
	$ql		= new cSQLListas();
		
	$myCab = new cTabla($ql->getListadoDeActividadesEconomicas($idsocio));
	$myCab->addEditar(USUARIO_TIPO_OFICIAL_CRED);
	$myCab->addEliminar(USUARIO_TIPO_OFICIAL_CRED);
	$myCab->OButton("TR.Verificar", "jsVerificarAE(_REPLACE_ID_)", $myCab->ODicIcons()->SALUD);
	$myCab->setKeyField("idsocios_aeconomica");
	return  $myCab->Show();
}
function jsaReRelaciones($idsocio){
	//Checar compatibilidad numerica entre los dependientes economicos
	$sqlL		= new cSQLListas();
	$cBenef		= new cTabla($sqlL->getListadoDeRelacionesPersonales($idsocio));
	$xTbl		= new cHTabla("idtblrels");$xHSel		= new cHSelect(); $xChk	= new cHCheckBox(); $xText	= new cHText(); $xText->setDivClass(""); $xChk->setDivClass("");
	$xBtn		= new cHButton();
	$xRuls		= new cReglaDeNegocio();
	$xUl		= new cHUl("idtools", "ul", "tags blue");
	
	$RelsSoloAct		= $xRuls->getValorPorRegla($xRuls->reglas()->PERSONAS_RELS_SOLOACTIV);		//regla de negocio
	
	$xUl->setTags("");
	$xUl->li($xBtn->getBasic("TR.Guardar", "jsGuardarReferencia()", $xBtn->ic()->GUARDAR, "idguardar", false, true));
	$xTbl->initRow();
	$xTbl->addTD($xText->getDeNombreDePersona());
	$xTbl->addTD($xHSel->getListaDeTiposDeRelaciones("", "", false, $RelsSoloAct)->get("") );
	$xTbl->addTD($xHSel->getListaDeTiposDeParentesco()->get("")  );
	$xTbl->addTD($xChk->get("TR.es dependiente_economico", "dependiente") );
	$xTbl->addRaw("<td class='toolbar-24'>". $xUl->get() . "</td>" );
	$xTbl->endRow();
		
	$cBenef->addEditar(USUARIO_TIPO_OFICIAL_CRED);
	$cBenef->addEliminar(USUARIO_TIPO_OFICIAL_CRED);
	
	$cBenef->setKeyField("idsocios_relaciones");
	
	$xLi	= new cSQLListas();
	$xT		= new cTabla($xLi->getListadoDeReferenciasBancarias($idsocio));
	$xT2	= new cTabla($xLi->getListadoDeReferenciasComerciales($idsocio));
	$xT->setKeyTable("socios_relaciones");
	$xT2->setKeyTable("socios_relaciones");
	$xT2->addEliminar(USUARIO_TIPO_OFICIAL_CRED);
	$xT->addEliminar(USUARIO_TIPO_OFICIAL_CRED);

	$TBen	= $cBenef->Show("TR.REFERENCIAS_PERSONALES");
	$TBen	= ($cBenef->getRowCount() <=0) ? "" : $TBen ; 
	$TBan	= $xT->Show("TR.REFERENCIAS_BANCARIAS");
	$TBan	= ($xT->getRowCount() <=  0) ? "" : $TBan;
	$TCom	= $xT2->Show("TR.REFERENCIAS_COMERCIALES");
	$TCom	= ($xT2->getRowCount() <= 0) ? "" : $TCom;
	
	return $xTbl->get() . $TBen . $TBan . $TCom ;
}

function jsaRePatrimonio($idsocio){
	$ql		= new cSQLListas();
	$myTab = new cTabla($ql->getListaDePatrimonioPorPersona($idsocio));
	$myTab->addEditar(USUARIO_TIPO_OFICIAL_CRED);
	$myTab->setKeyField("idsocios_patrimonio");
	
	return $myTab->Show();	
}

function jsaSetDocumentoVerificado(){ }
function jsaSetDocumentoFalso(){ }

function jsaValidarDocumentacion($persona){
	$xAml	= new cAMLPersonas($persona);
	$xAml->init($persona);
	$xAml->setVerificarDocumentosCompletos();
	$xAml->setVerificarDocumentosVencidos();
	return $xAml->getMessages(OUT_HTML);
}
function jsaValidarRiesgo($persona){
	$xAml	= new cAMLPersonas($persona);
	$xAml->init($persona);
	$xAml->setAnalizarNivelDeRiesgo();
	//$xAml->setVerificarDocumentosCompletos();
	//$xAml->setVerificarDocumentosVencidos();
	return $xAml->getMessages(OUT_HTML);
}

function jsaValidarPerfilTransaccional($persona){
	$xAml		= new cAMLPersonas($persona);
	$xAml->init();
	$validar	= false; //(MODO_DEBUG == true) ? true : false;
	$xAml->setVerificarPerfilTransaccional(false, $validar);
	$xAml->setVerificarOperacionesSemestrales();
	
	return $xAml->getMessages(OUT_HTML);
}

function jsaCumplimiento($idsocio){
	$xAl		= new cAml_alerts();
	$xlistas	= new cSQLListas();
	$sql		= $xlistas->getListadoDeAlertas(false, false, false, $idsocio);
	$xT			= new cTabla($sql);
	$xT->setKeyField( $xAl->getKey() );
	$xT->setKeyTable( $xAl->get() );
	//if(getEsModuloMostrado($tipo_de_usuario))
	if(getSePuedeMostrar(MMOD_AML, MQL_MOD)){
		$xT->addEditar();
	}
	if(getSePuedeMostrar(MMOD_AML, MQL_DEL)){
		$xT->addEliminar();
	}
	return $xT->Show();	
}


function jsaAddDescuento($idpersona, $descuento){
	$xSoc		= new cSocio($idpersona); $xSoc->init();
	$xSoc->setMontoAhorroPreferente($descuento);
	return $xSoc->getMessages();
}

/*function jsaAddDescuentoDesdeEmpresa($idpersona, $descuento){
	$xSoc		= new cSocio($idpersona);
	if($xSoc->init() == true){
		$xSoc->setMontoAhorroPreferente($descuento);
	}
	return $xSoc->getMessages();
}*/


function jsaSetEnviarParaAsociada($idpersona){
	$xSoc		= new cSocio($idpersona); $xSoc->init();
	$xSoc->setMontoAhorroPreferente(0);
	return $xSoc->getMessages();	
}

function jsaGetOperaciones($idpersona, $fecha){
	
}
function jsaGetListadoDeNominas($idempresa){
	$xEmp		= new cEmpresas($idEmpresa);
	$xF			= new cFecha();
	$ql			= new MQL();
	$xl			= new cSQLListas();	
}
/*function jsaActualizarEmpresa($idempresa){
	$xEmp	= new cEmpresas($idempresa);
	$xEmp->init();
	$xEmp->setActualizarPorPersona();
	return $xEmp->getMessages(OUT_HTML);
}*/

function jsaActualizarSucursal($idsucursal){
	$xSuc	= new cSucursal($idsucursal);
	if($xSuc->init() == true){
		$xSuc->setActualizarPorPersona();
	}
	return $xSuc->getMessages(OUT_HTML);
}
function jsaActualizarUsuario($idusuario){
	$xUser	= new cSystemUser($idusuario);	
	$xUser->setActualizarPorPersona();
	return $xUser->getMessages(OUT_HTML);
}
$jxc ->exportFunction('jsaRePatrimonio', array('idsocio' ), "#tab-patrimonio");
$jxc ->exportFunction('jsaReActividadE', array('idsocio' ), "#tab-actividad");
$jxc ->exportFunction('jsaReRelaciones', array('idsocio' ), "#tab-relaciones");
$jxc ->exportFunction('jsaCumplimiento', array('idsocio' ), "#tab-cumplimiento");
$jxc ->exportFunction('jsaReVivienda', array('idsocio' ), "#tab-domicilio");
$jxc ->exportFunction('jsaValidarDocumentacion', array('idsocio' ), "#idavisos");
$jxc ->exportFunction('jsaValidarRiesgo', array('idsocio' ), "#idavisos");
$jxc ->exportFunction('jsaValidarPerfilTransaccional', array('idsocio' ), "#idavisos");

$jxc ->exportFunction('jsaAddDescuento', array('idsocio', 'iddescuento'), "#idavisos");
//$jxc ->exportFunction('jsaAddDescuentoDesdeEmpresa', array('idmodificado', 'idcantidad'), "#idavisos");
$jxc ->exportFunction('jsaSetEnviarParaAsociada', array('idsocio' ), "#idavisos");
//$jxc ->exportFunction('jsaActualizarEmpresa', array('idempresa' ), "#idavisos");
$jxc ->exportFunction('jsaActualizarSucursal', array('idsucursal' ), "#idavisos");
$jxc ->exportFunction('jsaActualizarUsuario', array('idusuario' ), "#idavisos");

$jxc ->process();

$xHP->addJsFile("../jsrsClient.js");

echo $xHP->getHeader();

echo $xJsB->setIncludeJQuery(); 

//$xJsB	= new jsBasicForm("extrasocios");
?>
<body>
<?php

if ( setNoMenorQueCero($idsocio) <= DEFAULT_SOCIO){
	$xFRM	= new cHForm("extrasocios", "socios.panel.frm.php");
	$xBtn	= new cHButton();
	$xTxt	= new cHText();
	
	$xFRM->setTitle( $xHP->getTitle() ); 
	$xFRM->addPersonaBasico();
	$xFRM->addSubmit();
	
	echo $xFRM->get();

} else {
	$xSoc 		= new cSocio($idsocio, true);
	if($xSoc->init() == true){
		getPersonaEnSession($idsocio);
	}
	$xHTabs		= new cHTabs();
	$xBtn		= new cHButton("");
	$xFRM		= new cHForm("extrasocios", "");
	$xHSel		= new cHSelect();
	$xNotif		= new cHNotif();
	$xFRM->OButton("TR.Recargar", "jsRecargar()", $xFRM->ic()->RECARGAR, "", "blue");
	$xFRM->addHElem( $xSoc->getFicha(true) );

	if($xSoc->getPermisoParaOperar() == true){
		$xFRM->addPersonaComandos($idsocio);
	} else {
		$xLog->add("ERROR\tEsta Persona esta en Baja\r\n");
		//TODO: Agregar reactivacion de personas
	}
	//Agregar Sucursal
	$xLog->add("OK\tSucursal: " . $xSoc->getSucursal() . "\r\n");
	
	if(getEsModuloMostrado(USUARIO_TIPO_OFICIAL_CAPT) == true OR getEsModuloMostrado(USUARIO_TIPO_OFICIAL_CRED) == true){ 
		//Agregar otra opciones
		$xFRM->addToolbar( $xBtn->getBasic("TR.Actualizar Datos", "updateDat()", "editar", "edit-socio", false ) );
		if(PERSONAS_CONTROLAR_POR_EMPRESA == true AND MODULO_CAPTACION_ACTIVADO == true){
			$xFRM->addToolbar( $xBtn->getBasic("TR.Agregar Descuento Solicitado", "jsAddDescuento()", "dinero", "edit-descuento", false ) );
		}
		if(PERSONAS_COMPARTIR_CON_ASOCIADA == true){
			$xFRM->addToolbar( $xBtn->getBasic("TR.Enviar a Empresa Asociada", "jsaSetEnviarParaAsociada()", $xBtn->ic()->EXPORTAR , "edit-aasoc", false ) );
		}
	}

	//===============================================================================	
	$setSql4	= $xLi->getListadoDeNotas($idsocio);
	$c4Tbl 		= new cTabla($setSql4);
	
	$c4Tbl->setKeyField("idsocios_memo");
	$c4Tbl->addEliminar(USUARIO_TIPO_OFICIAL_CRED);
	$c4Tbl->addEditar(USUARIO_TIPO_OFICIAL_CRED);
	$c4Tbl->setEventKey("var xP=new PersGen();xP.getVerNota");
	$HNotas		= $c4Tbl->Show();
	if($c4Tbl->getRowCount()>0){ $xHTabs->addTab($xFRM->lang("NOTAS"), $HNotas, "tab-notas"); }
	
	$xHTabs->addTab("TR.DOMICILIO", "", "tab-domicilio" );
	$xHTabs->addTab(PERSONAS_TITULO_PARTES, "", "tab-relaciones");
	$xHTabs->addTab("TR.ACTIVIDAD_ECONOMICA", "", "tab-actividad" );
	$xHTabs->addTab("TR.PATRIMONIO", "", "tab-patrimonio");
	//=======================================================================
	$cnt		= "";
	$xB			= new cBases();
	$mems		= ($xSoc->getEsPersonaFisica() == true) ? $xB->getMembers_InArray(false, BASE_DOCTOS_PERSONAS_FISICAS) : $xB->getMembers_InArray(false, BASE_DOCTOS_PERSONAS_MORALES);
	$xTblD		= new cTabla($xLi->getListadoDePersonasDoctos($idsocio, true), 0, "iddoctos");
	$xTblD->addEliminar(USUARIO_TIPO_GERENTE);
	$xTblD->setKeyField("clave");
	$xTblD->setKeyTable("personas_documentacion");
	$xTblD->setOmitidos("archivo_de_documento");
	$xTblD->OButton("TR.VER", "var xP=new PersGen();xP.getDocumento({id:" . HP_REPLACE_ID . "})", $xTblD->ODicIcons()->VER, "idview");
	$xHTabs->addTab("TR.DOCUMENTOS", $xTblD->Show()); //tabs
	if(getEsModuloMostrado(USUARIO_TIPO_OFICIAL_AML) == true){
		$xDiv3		= new cHDiv("tx1", "msgcumplimiento");
		
		$xFRM->OButton("TR.validar documentos", "jsaValidarDocumentacion()", "documentos", "idvalidadoc" );
		$xFRM->addToolbar( $xBtn->getBasic("TR.validar perfil_transaccional", "jsaValidarPerfilT()", "perfil", "validaperfil", false ) );
		$xFRM->addToolbar( $xBtn->getBasic("TR.validar riesgo", "jsaValidarRiesgo()", "riesgo", "validariesgo", false ) );
		$xFRM->addToolbar( $xBtn->getBasic("TR.Actualizar Nivel de Riesgo", "jsActualizarNivelDeRiesgo($idsocio)", "riesgo", "actualizarriesgo", false ) );
		
		$xFRM->OButton("TR.Consulta en LISTAS", "var xAML = new AmlGen(); xAML.getConsultaListas($idsocio)", $xFRM->ic()->REGISTROS);
		///$xFRM->OButton("TR.Consulta en PEPS", "var xAML = new AmlGen(); xAML.getConsultaPEPS($idsocio)", $xFRM->ic()->REGISTROS);
		
		$xHTabs->addTab("TR.cumplimiento", $xDiv3->get(), "tab-cumplimiento"); //tab6
		$jsTabs	.= ",\n selected: 6\n";
	}
	//Arbol de relaciones y perfil transaccional
	if(MODULO_AML_ACTIVADO == true){
		$xFRM->OButton("TR.ARBOL_DE_RELACIONES", "jsSigmaRelaciones()", $xFRM->ic()->EXPORTAR);
		$xT		= new cTabla($xLi->getListadoDePerfil($idsocio) );
		$xT->addEliminar();
		$xHTabs->addTab("TR.perfil_transaccional", $xT->Show() );
		
		//Agregar Consulta Listas
		$ttl	= "";
		$xTLNI	= new cTabla($xLi->getListadoDePersonasConsultasLInt($idsocio));
		$xTLNI->setOmitidos("nombre");$xTLNI->setOmitidos("persona");$xTLNI->setOmitidos("observaciones"); $xTLNI->setTitulo("clave_interna", "CLAVE"); $xTLNI->setTitulo("estatus", "ESTATUSACTIVO");
		$ttl 	.= $xTLNI->Show("TR.LISTA_NEGRA INTERNA");
		$xTLBI	= new cTabla($xLi->getListadoDePersonasConsultasBInt($idsocio));
		$xTLBI->setOmitidos("nombre");$xTLBI->setTitulo("clave_de_motivo", "MOTIVO");
		
		$ttl 	.= $xTLBI->Show("TR.LISTA_OMITIDOS");
		$xHTabs->addTab("TR.LISTASINTERNAS", $ttl );
	}
	if(getEsModuloMostrado(USUARIO_TIPO_OFICIAL_CRED)){
		$xFRM->OButton("TR.Riesgo de Credito", "var xP= new PersGen();xP.getRiesgoDeCredito($idsocio)", $xFRM->ic()->RIESGO);
	}
//================= Empresa con Convenio
	if($xSoc->getEsEmpresaConConvenio(true) == true){
		/*$xT2		= new cHTabs("idcomoempresa");
		$idempresa	= $xSoc->getOEmpresa()->getClaveDeEmpresa();
		$xFRM->addEmpresaComandos($idempresa);

		$xTCreds	= new cTabla($xLi->getListadoDeCreditos(false, false, false, false, " AND (`creditos_solicitud`.`persona_asociada` = $idempresa) ", false), 2 );
		$xTPers		= new cTabla($xLi->getListadoDeSocios(" (`socios_general`.`dependencia` = $idempresa)  ") );
		$xTAhorro	= new cTabla($xLi->getListadoDeIncidenciasAhorro($idempresa));
		//========================== Tabla de periodos para empresas
		$xTPeriodo	= new cTabla($xLi->getListadoDePeriodoPorEmpresa($idempresa) );
		$xTPeriodo->setTdClassByType();
		
		$xTPeriodo->setEventKey("var xG = new EmpGen(); xG.getOrdenDeCobranza");
		$xTPeriodo->OButton("TR.Panel", "var xG = new EmpGen(); xG.getTablaDeCobranza(" . HP_REPLACE_ID . ")", $xFRM->ic()->CONTROL);
		$xTPeriodo->addEditar(USUARIO_TIPO_CAJERO);
		
		$xTCreds->setTdClassByType(); $xTPers->setTdClassByType(); $xTAhorro->setTdClassByType();
		$xTPers->setWidthTool("200px");
		if(MODULO_CAPTACION_ACTIVADO == true){
			$xModAhorro	= "\$xS=new cSocio(_REPLACE_ID_,true);PHP::\"<input value='\" . \$xS->getAhorroPreferente() . \"' type='number' id='id_REPLACE_ID_' onchange='jsModificarAhorro(this,_REPLACE_ID_)' />\";";
			$xTPers->addEspTool($xModAhorro);
		}
		if(PERSONAS_CONTROLAR_POR_EMPRESA == true){
			$xT2->addTab("TR.Empleados", $xTPers->Show());
			$xTCreds->setFootSum(array(8 => "saldo"));
			$xT2->addTab("TR.Creditos por empresa", $xTCreds->Show());
			if(MODULO_CAPTACION_ACTIVADO == true){
				//Ahorro por Empresa
				$xT2->addTab("TR.Ahorro por empresa", $xTAhorro->Show());
			}
			$xT2->addTab("TR.Periodos de Empresa", $xTPeriodo->Show());
		}
		//$xHTabs->addTab("TR.empresa $idempresa", $xT2->get() ); //tab4
		//== reporte de pagos
		$xT		= new cTabla($xLi->getListadoDePresupuestoPorPagar($idsocio));
		$xT->setFootSum(array(
				10 => "monto_de_cheque"
		));
		$xT2->addTab("TR.Pagos Pendientes", $xT->Show());
		$xHTabs->addTab("TR.empresa $idempresa", $xT2->get() ); //tab4
				
		$xFRM->OButton("TR.Cedula de Ahorro", "jsGetCedulaDeAhorro()", "deposito");
		$xFRM->OButton("TR.Orden de Ahorro", "jsGetEmpresaCaptacion()", $xFRM->ic()->TAREA);
		$xFRM->OButton("TR.Excel de Ahorro", "jsCedulaAhorroExcel()", $xFRM->ic()->EXCEL);
		$xFRM->OButton("TR.Actualizar Empresa", "jsaActualizarEmpresa()", $xFRM->ic()->EJECUTAR);
		$xFRM->OHidden("idempresa", $idempresa);*/
	}
	if($xSoc->getEsSucursal() == true){
		$xFRM->OButton("TR.Actualizar Sucursal", "jsaActualizarSucursal()", $xFRM->ic()->EJECUTAR);
		$xFRM->OHidden("idsucursal", $xSoc->getIDSucursalAsociada());
	}
	if($xSoc->getEsUsuario(true) == true){
		$xFRM->OButton("TR.Actualizar Usuario", "jsaActualizarUsuario()", $xFRM->ic()->EJECUTAR);
		$xFRM->OHidden("idusuario", $xSoc->getOUsuario()->getID());
		if($xSoc->getOUsuario()->getID() == getUsuarioActual()){
			$xFRM->OButton("TR.Actualizar password", "jsActualizarPassword($idsocio)", $xFRM->ic()->PASSWORD);
		}
	}
	//Agregar convenios
	$InfoCreds			= "";
	$xTListaCreds		= new cTabla($xLi->getListadoDeCreditos($idsocio), 2);
	$xTListaCreds->OButton("TR.Panel", "jsGoToPanelCredito(" . HP_REPLACE_ID . ")", $xTListaCreds->ODicIcons()->CONTROL);
	$xTListaCreds->OButton("TR.PLAN_DE_PAGOS", "var xC=new CredGen();xC.getImprimirPlanPagosPorCred(" . HP_REPLACE_ID . ")", $xTListaCreds->ODicIcons()->IMPRIMIR);
	$xTListaCreds->setFootSum(array(8 => "monto", 9 => "saldo"));
	$LVig				= $xTListaCreds->Show("TR.VIGENTE");
	$InfoCreds			.= ($xTListaCreds->getRowCount()<= 0) ? "" : $LVig;
	
	//Creditos por Autorizar
	$LVig				= $xODT->getCreditosPorMinistrar(false, $idsocio,"TR.AUTORIZADO");
	$InfoCreds			.= ($xODT->getNumeroItems()<=0) ? "" : $LVig;
	$LVig				= $xODT->getCreditosPorAutorizar(false, $idsocio, "TR.SOLICITADO");
	$InfoCreds			.= ($xODT->getNumeroItems()<=0) ? "" : $LVig;
	//Creditos Pagados
	$xTListaCredsP		= new cTabla($xLi->getListadoDeCreditosPagados($idsocio, false, true), 0);
	$xTListaCredsP->OButton("TR.Panel", "jsGoToPanelCredito(" . HP_REPLACE_ID . ")", $xTListaCredsP->ODicIcons()->CONTROL);
	//$xTListaCreds->OButton("TR.PLAN_DE_PAGOS", "var xC=new CredGen();xC.getImprimirPlanPagosPorCred(" . HP_REPLACE_ID . ")", $xTListaCreds->ODicIcons()->IMPRIMIR);
	$xTListaCredsP->setFootSum(array(8 => "monto", 9 => "saldo"));
	$LVig				= $xTListaCredsP->Show("TR.PAGADO");
	$InfoCreds			.= ($xTListaCredsP->getRowCount()<= 0) ? "" : $LVig;
	
	$xHTabs->addTab("TR.Creditos", $InfoCreds );
	
	
	if(MODULO_CAPTACION_ACTIVADO == true){
		//agregar cuenta de ahorro
		$xTListaCapt	= new cTabla($xLi->getListadoDeCuentasDeCapt($idsocio));
		$xTListaCapt->OButton("TR.Panel", "var xC= new CaptGen();xC.goToPanel(" . HP_REPLACE_ID . ")", $xFRM->ic()->CONTROL);
		$xHTabs->addTab("TR.Captacion", $xTListaCapt->Show() );
	}
	//Actualizar Descuentos
	$xDiv2				= new cHDiv("inv", "iddivdescuento");
	$xFRM10 			= new cHForm("frmdescuento");
	$xFRM10->addSubmit("", "jsGuardarDescuento()", "jsCancelarAccion()");
	$xFRM10->OMoneda("iddescuento", 0, "TR.Monto");
	//======================================== 			RECIBOS
	$xFRM->OButton("TR.ESTADO_DE_CUENTA OTROSINGRESOS", "var xP=new PersGen(); xP.getReportePagosNoDoc($idsocio)", $xFRM->ic()->REGISTROS);
	//Agregar Listado de Recibos
	$xLi->setInvertirOrden();
	$cTblx			= new cTabla($xLi->getListadoDeRecibosConDocto("", $idsocio));
	$cTblx->setKeyField("idoperaciones_recibos");
	$cTblx->setTdClassByType();
	$cTblx->setEventKey("jsGoPanelRecibos");
	$xHTabs->addTab("TR.RECIBOS", $cTblx->Show());
	
	//======================================== 			Tabla de Operaciones
	$sql		= $xLi->getListadoDeOperaciones($idsocio);
	$cEdit		= new cTabla($sql);
	$cEdit->addTool(SYS_UNO);
	$cEdit->addTool(SYS_DOS);
	$cEdit->setTdClassByType();
	$cEdit->setKeyField("idoperaciones_mvtos");
	$HOperaciones=$cEdit->Show();
	if($cEdit->getRowCount()>0){ $xHTabs->addTab("TR.Operaciones", $HOperaciones); }
	
	//======================================== AML

	/*Validacion*/
	if(MODO_DEBUG == true){
		$xHTabs->addTab("TR.Validacion", $xSoc->getValidacion(OUT_HTML));
		$xFRM->OButton("TR.Reporte SIC", "jsGetCirculoDeCredito()", $xBtn->ic()->REPORTE);
	}
	
	if((MODO_CORRECION == true OR MODO_MIGRACION == true OR MODO_DEBUG == true) AND (getUsuarioActual(SYS_USER_NIVEL) >= USUARIO_TIPO_GERENTE) ){
		$xStats	= new cPersonasEstadisticas($idsocio);
		$xStats->initDatosDeCredito(true);
		$xFRM->OButton("TR.BAJA PERSONA", "var xP=new PersGen();xP.setBaja($idsocio)", $xFRM->ic()->PARAR);
		if($xStats->getTotalCompromisos()== 0){
			$xFRM->OButton("TR.ELIMINAR PERSONA", "jsEliminarPersona($idsocio)", $xFRM->ic()->ELIMINAR);
		} else {
			//$xFRM->addAviso(, "", true, "warning");
			$xLog->add("WARN\tLa persona tiene " . $xStats->getTotalCompromisos() . " Contratos Activos, no se debe eliminar\r\n");
			if(MODO_DEBUG == true){
				$xFRM->OButton("TR.ELIMINAR PERSONA", "jsEliminarPersona($idsocio)", $xFRM->ic()->ELIMINAR);
			}
		}
		
		//Agregar Lista de Parecidos
		$sqlTT	= $xLi->getListadoDeBusquedaSocios($xSoc->getNombre(), $xSoc->getApellidoPaterno(), $xSoc->getApellidoMaterno(), "", "", $xSoc->getCodigo());
		$xTT2	= new cTabla($sqlTT);
		$xTT2->OButton("TR.Unificar", "var xP=new PersGen();xP.setUnificar($idsocio, ". HP_REPLACE_ID .  ")", $xFRM->ic()->EXPORTAR);
		$xHTabs->addTab("TR.Validacion", $xTT2->Show());
	}
	//===================================== Oficial propietario
	$xUsr		= new cSystemUser($xSoc->getClaveDeUsuario());
	if($xUsr->init() == true){
		$xLog->add("OK\tCreado por : " . $xUsr->getNombreCompleto() . "\r\n");
	}
	//====================================== Datos extranjero
	if($xSoc->getEsExtranjero() == true){
		$xFRM->OButton("TR.DATOS_EXTRANJEROS", "var xG=new PersGen();xG.setFormaDatosExt($idsocio)", $xFRM->ic()->GRUPO);
	}
	$xDiv2->addHElem($xFRM10->get());
	//===================================== Recibos de Otros Ingresos
	$sql98		= "SELECT
			`operaciones_mvtos`.`idoperaciones_mvtos`     AS `clave`,
			`operaciones_recibos`.`idoperaciones_recibos` AS `recibo`,
			`operaciones_recibos`.`fecha_operacion`       AS `fecha`,
			`operaciones_tipos`.`descripcion_operacion`   AS `operacion`,
			`operaciones_recibos`.`tipo_pago`             AS `tipo_de_pago`,
			`operaciones_mvtos`.`afectacion_real`         AS `monto`
		FROM
			`operaciones_mvtos` `operaciones_mvtos` 
				INNER JOIN `operaciones_recibos` `operaciones_recibos` 
				ON `operaciones_mvtos`.`recibo_afectado` = `operaciones_recibos`.
				`idoperaciones_recibos` 
					INNER JOIN `operaciones_recibostipo` `operaciones_recibostipo` 
					ON `operaciones_recibos`.`tipo_docto` = `operaciones_recibostipo`.
					`idoperaciones_recibostipo` 
						INNER JOIN `operaciones_tipos` `operaciones_tipos` 
						ON `operaciones_mvtos`.`tipo_operacion` = `operaciones_tipos`.
						`idoperaciones_tipos` 
		WHERE
			(`operaciones_mvtos`.`socio_afectado` =$idsocio)  AND
			(`operaciones_tipos`.`recibo_que_afecta` =" . RECIBOS_TIPO_OINGRESOS . ") 
		ORDER BY
			`operaciones_recibos`.`fecha_operacion`";
	$xTT98	= new cTabla($sql98,1);
	
	$xTT98->OButton("TR.RECIBO", "var xR=new RecGen();xR.formato(". HP_REPLACE_ID .  ")", $xFRM->ic()->IMPRIMIR);
	$xHTabs->addTab("TR.OTROSINGRESOS", $xTT98->Show());
	
	//===================================== APORTACIONES y CUOTAS
	if(PERSONAS_CONTROLAR_POR_APORTS == true ){
		//if($xSoc->getMembresiaDiaPag() == $xF->dia()){
		$xFRM->OButton("TR.COBRO MEMBRESIA", "var xP=new PersGen();xP.setCobroMembresia($idsocio," . $xF->mes() . ");", $xFRM->ic()->COBROS);
		$xTLC	= new cTabla($xLi->getListadoDePersonaPerfilCuotas($idsocio));
		$xTLC->setOmitidos("clave_de_persona");
		$xTLC->setOmitidos("fecha_de_aplicacion");
		$xHTabs->addTab("TR.MEMBRESIA", $xTLC->Show());
		//}
	}
	
	//=====================================
	$xFRM->addHTML($xHTabs->get());
	$xFRM->addHTML($xDiv2->get());
	
	
	$xFRM->OHidden("idsocio", $idsocio); $xFRM->OHidden("idmodificado", ""); $xFRM->OHidden("idcantidad", "0");

	$xFRM->addAviso($xLog->getMessages(), "idavisos");
	

	echo $xFRM->get();
}
?>
</body>
<script>
var mSocio		= <?php echo  ($idsocio === false) ? "0" : $idsocio; ?>;
var xG			= new Gen();
var xPG			= new PersGen();
var xRec		= new RecGen();
if (mSocio != 0) {

	session(ID_PERSONA, mSocio); //Asignar Socio en Session

$(function() {
	$( "#tab" ).tabs({
			select: function(event, ui){
				selected = ui.panel.id;
					switch (selected){
					case "tab-notas":
						
						break;
					case "tab-domicilio":
						jsaReVivienda();
						break;
					case "tab-relaciones":
						jsaReRelaciones();
						break;
					case "tab-actividad":
						jsaReActividadE();
						break;
					case "tab-patrimonio":
						jsaRePatrimonio();
						break;
					case "tab-cumplimiento":
						jsaCumplimiento();
						break;
				}
		    }<?php echo $jsTabs; ?>	
		});
});

}

function addPatrim(){
	var srURL = "../frmsocios/frmsociospatrimonio.php?socio=<?php echo $idsocio; ?>";
	xG.w({ url: srURL, tiny : true });
}
function updateDat(){
	var srUp = "../frmsocios/frmupdatesocios.php?persona=<?php echo $idsocio; ?>";
	xG.w({ url: srUp, tab: true });
}
function addHistorial(){
	var sDiv	= "<?php echo STD_LITERAL_DIVISOR; ?>";
	var srURL 	= "../frmsocios/frmhistorialdesocios.php?d=1" + sDiv + <?php echo $idsocio; ?> + sDiv + "1" + sDiv + "99" + sDiv + "NOTA_DEL_SOCIO" ;
	xG.w({ url: srURL, tiny : true });
}	
function jsVerificar(id){
	var URIL	= "../frmsocios/socios.verificacion.frm.php?t=d&s=" + mSocio +"&i=" + id;
	xG.w({ url: URIL, tiny : true });		
}
function jsVerificarAE(id){
	var URIL	= "../frmsocios/socios.verificacion.frm.php?t=t&s=" + mSocio +"&i=" + id;
	xG.w({ url: URIL, tiny : true });		
}
function jsUp(t, f, id) {
	var url = "../utils/frm8db7028bdcdf054882ab54f644a9d36b.php?t=" + t + "&f=" + f + "=" + id;
	xG.w({ url: url, tiny : true });
}
function jsDel(t, f, id) {
	var siXtar = confirm("Desea en Realidad Eliminar \n el Registro Seleccionado");
	if(siXtar==true){
		var sURL = "../utils/frm9d23d795f8170f495de9a2c3b251a4cd.php?t=" + t + "&f=" + f + "=" + id;
			delme = window.open(sURL, "", "width=300,height=300,scrollbars=yes,dependent");
			//delme.focus();
			document.getElementById("tr-" + t + "-" + id).innerHTML = "";
	} else {
			if( window.console ) { window.console.log( '' ); }
			window.statusText = "Operacion Cancelada";

	}
}
function jsAddDocumentos(){
	var sURL = "../frmsocios/personas_documentos.frm.php?persona=" + mSocio;
	xG.w({ url: sURL, tiny : true });
}
function jsToImage(uxl){
	var xrl		= "../frmsocios/documento.png.php?persona=" + uxl;
	xG.w({ url: xrl, tiny : true });  
}
function jsaValidarPerfilT(){	jsaValidarPerfilTransaccional();	}
function jsActualizarNivelDeRiesgo(id){	
	var xML = new AmlGen(); xML.goToCambiarNivel(id);
}
/*function jsModificarAhorro(evt, idpersona){
	if(flotante(evt.value) >= 0 ){
		$("#idmodificado").val(idpersona);
		$("#idcantidad").val(flotante(evt.value));
		var siguarda	= confirm("DESEA GUARDAR EL DESCUENTO PREFERENTE POR " + evt.value);
		if(siguarda){ jsaAddDescuentoDesdeEmpresa();	}
	}
}*/
function jsCancelarAccion(){	$(window).qtip("hide");    }
function jsAddDescuento(){ getModalTip(window, $("#iddivdescuento"), xG.lang(["actualizar", "descuento"]));	}
function jsGuardarDescuento(){	jsaAddDescuento();	setTimeout("jsCancelarAccion()", 2000);	}
/*

function jsGetCedulaDeAhorro(){
	var EmpG	= new EmpGen();
	var idemp	= $("#idempresa").val();
	EmpG.getCedulaAhorro(idemp);
}
function jsGetEmpresaCaptacion(){
	var EmpG	= new EmpGen();
	var idemp	= $("#idempresa").val();
	EmpG.getTablaDeCaptacion(idemp);
}*/
/*function jsCedulaAhorroExcel(){ 
var idemp	= $("#idempresa").val();
var xrl		= "../frmempresas/layout-cedula.frm.php?empresa=" + idemp;
xG.w({ url: xrl, tiny : true }); 	
}*/
function jsGetCirculoDeCredito(){
	var xrl		= "../rptlegal/circulo_de_credito.rpt.php?persona=" + mSocio;
	xG.w({ url: xrl, tab : true });  
}
function jsGetOperaciones(){ 	}
function jsGoToPanelCredito(idx){ var xCred = new CredGen(); xCred.goToPanelControl(idx); }
//function jsListaDeNominas(idnomina){ var EmpG	= new EmpGen(); EmpG.getOrdenDeCobranza(idnomina);	}
function jsRecargar(){ window.location = "socios.panel.frm.php?persona=" + mSocio; }

function jsGuardarReferencia(){
	var idrelacionado		= $("#idpersona").val();
	var idtipoderelacion	= $("#idtipoderelacion").val();
	var idtipodeparentesco	= $("#idtipodeparentesco").val();
	var stat				= $('#dependiente').prop('checked');
	
	xPG.addRelacion({ persona : mSocio, relacionado : idrelacionado, tipo : idtipoderelacion, parentesco : idtipodeparentesco, depende : stat, callback : jsGetRelaciones });
	$("#idpersona").val(0);
}
function jsGetRelaciones(){ jsaReRelaciones(); }
function jsSigmaRelaciones(){ 
	var xrl		= "../frmsocios/socios.relaciones.sigma.frm.php?persona=" + mSocio;
	xG.w({ url: xrl, tiny : true }); 	
}
function jsGoPanelRecibos(id){ xRec.panel(id); }
function jsEliminarPersona(id){
	xPG.eliminar(id);
}
function jsActualizarPassword(){ 
	var xrl		= "../frmsocios/socios.usuario.frm.php?persona=" + mSocio;
	xG.w({ url: xrl, tiny : true }); 	
}

</script>
<?php
echo $xJsB->get();
$jxc ->drawJavaScript(false, true);
?>
</html>