<?php

if (!defined("WHMCS")) {
    die("This file cannot be accessed directly");
}

// These are all .br TLDs that can be registered with either CPF or CNPJ, not including TLDs requiring documentation such as org.br.
unset ($registrobr_GenericTLDs);
unset ($registrobr_CPFTLDs);
unset ($registrobr_CNPJTLDs);
unset ($registrobr_AllTLDs);

$registrobr_GenericTLDs = array ( ".app.br", ".art.br", ".com.br" , ".dev.br", ".eco.br", ".log.br", ".net.br", ".ong.br", ".tec.br", ".9guacu.br", ".abc.br", ".aju.br", ".anani.br", ".aparecida.br", ".barueri.br", ".belem.br", ".bhz.br", ".boavista.br", ".bsb.br", ".campinagrande.br", ".campinas.br", ".caxias.br", ".curitiba.br", ".feira.br", ".floripa.br", ".fortal.br", ".foz.br", ".goiania.br", ".gru.br", ".jab.br", ".jampa.br", ".jdf.br", ".joinville.br", ".londrina.br", ".macapa.br", ".maceio.br", ".manaus.br", ".maringa.br", ".morena.br", ".natal.br", ".niteroi.br", ".osasco.br", ".palmas.br", ".poa.br", ".pvh.br", ".recife.br", ".ribeirao.br", ".rio.br", ".riobranco.br", ".riopreto.br", ".salvador.br", ".sampa.br", ".santamaria.br", ".santoandre.br", ".saobernardo.br", ".saogonca.br", ".sjc.br", ".slz.br", ".sorocaba.br", ".the.br", ".udi.br", ".vix.br");
// .emp.br not listed since it requires additional MoU
$registrobr_CPFTLDs = array ( ".blog.br", ".flog.br", ".vlog.br", ".wiki.br", ".adm.br", ".adv.br", ".arq.br", ".ato.br", ".bib.br", ".bio.br", ".bmd.br", ".cim.br", ".cng.br", ".cnt.br", ".coz.br", ".des.br", ".det.br", ".ecn.br", ".enf.br", ".eng.br", ".eti.br", ".fnd.br", ".fot.br", ".fst.br", ".geo.br", ".ggf.br", ".jor.br", "lel.br", ".mat.br", ".med.br", ".mus.br", ".not.br", ".ntr.br", ".odo.br", ".ppg.br", ".pro.br", ".psc.br", ".qsl.br", ".rep.br", ".slg.br", ".taxi.br", ".teo.br", ".trd.br", ".vet.br", ".zlg.br");
// .nom.br not listed due to dual dot constraint

$registrobr_CNPJTLDs = array ( ".agr.br", ".esp.br", ".etc.br", ".far.br", ".imb.br", ".ind.br", ".inf.br", ".radio.br", ".rec.br", ".srv.br", ".tmp.br", ".tur.br", ".tv.br");
// .br, .am.br, .coop.br, .fm.br, .g12.br, .gov.br, .mil.br, .org.br, .psi.br, .b.br, .def.br, .jus.br, .leg.br, .mp.br and tc.br not listed due to eligibility requirements

$registrobr_AllTLDs = array_merge ($registrobr_GenericTLDs, $registrobr_CPFTLDs, $registrobr_CNPJTLDs);


