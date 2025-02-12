select a.codofic, a.codresp, a.nomresp, count(*) ;
from resp a, actual b ;
where str(a.codofic)+str(a.codresp)=str(b.codofic)+str(b.codresp)