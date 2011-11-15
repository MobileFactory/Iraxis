program nmisync;

uses
  Forms,
  frm_nmisync in 'frm_nmisync.pas' {Form1};

{$R *.res}

begin
  Application.Initialize;
  Application.MainFormOnTaskbar := True;
  Application.CreateForm(TForm1, Form1);
  Application.Run;
end.
