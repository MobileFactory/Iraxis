unit frm_nmisync;

interface

uses
  Windows, Messages, SysUtils, Variants, Classes, Graphics, Controls, Forms,
  Dialogs, vg_controls, vg_scene, ExtCtrls, nmi_s3storage;

type
  TForm1 = class(TForm)
    vgScene1: TvgScene;
    Root1: TvgBackground;
    HudWindow1: TvgHudWindow;
    HudLabel1: TvgHudLabel;
    Label1: TvgLabel;
    Timer1: TTimer;
    procedure Timer1Timer(Sender: TObject);
    procedure FormShow(Sender: TObject);
  private
    { Private declarations }
  public
    { Public declarations }
  end;

var
  Form1: TForm1;


implementation

{$R *.dfm}

type
 S3Thread=class(TThread)
  private
    var s3:TS3STorage;
    var des:TMemoryStream;
  protected
    procedure Execute;override;
    procedure AppExit;
 end;


procedure S3Thread.Execute;
  begin
  inherited;
  des:=TMemoryStream.Create;
  des.Clear;
  des.LoadFromFile(ExtractFileDir(Application.ExeName)+'\'+ParamStr(3));
  s3:=TS3STorage.Create(ParamStr(1),ParamStr(2));
  s3.PutS3Object(ParamStr(4),ParamStr(5),des,true);
  des.Free;
  s3.Free;
  Synchronize(AppExit);
end;

procedure S3Thread.AppExit;
begin
  Application.Terminate;
end;

procedure TForm1.FormShow(Sender: TObject);
begin
  if ParamCount<>5 then begin
    vgScene1.Free;
    Timer1.Enabled:=false;
    Timer1.Free;
    ShowMessage('Usage: nmisync.exe [S3PUBLIC_KEY] [S3PRIVATE_KEY] [LOCAL_FILE_NAME] [BUCKLET_NAME] [TARGET_FILE_NAME]');
    Application.Terminate;
   end;
end;

procedure TForm1.Timer1Timer(Sender: TObject);
  var MyS3Thread:S3Thread;
begin
  Timer1.Enabled:=false;
  if not fileexists(ExtractFileDir(Application.ExeName)+'\'+ParamStr(3)) then begin
    ShowMessage('File nmi.zip not found!');
    Application.Terminate;
    halt;
  end;
  MyS3Thread:=S3Thread.Create(True);
  MyS3Thread.FreeOnTerminate:=True;
  MyS3Thread.Resume;
end;
end.
