SELECT DISTINCT
ptcr.tumor_size,
ptcr.nodes_assessed_ind,
ptcr.nodes_cytokeratin_pos,
ptcr.nodes_examined,
ptcr.nodes_pos,
ptcr.er_status,
ptcr.pr_status,
ptcr.her2neu_ind,
ptcr.her2neu_status_typ,
ptcr.dcis_status_typ,
--ltc.lookup_typ,
--ltc.lookup_desc,
Diagnosis.*

FROM
Patient,
Diagnosis,
varianenm.dbo.pt pt,
varianenm.dbo.pt_dx_cncr ptcr,
varianenm.dbo.lookup_typ_culture ltc

WHERE
Patient.PatientSer = pt.patient_ser
AND Diagnosis.PatientSer = Patient.PatientSer
AND pt.pt_id = ptcr.pt_id
AND Patient.PatientId = '5262528'
--AND ptcr.her2neu_status_typ = ltc.lookup_typ
--AND ltc.table_name ='her2neu_method_typ'