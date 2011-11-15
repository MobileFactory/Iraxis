object Form1: TForm1
  Left = 0
  Top = 0
  BorderStyle = bsNone
  Caption = 'Form1'
  ClientHeight = 338
  ClientWidth = 651
  Color = clBtnFace
  Font.Charset = DEFAULT_CHARSET
  Font.Color = clWindowText
  Font.Height = -11
  Font.Name = 'Tahoma'
  Font.Style = []
  OldCreateOrder = False
  Position = poScreenCenter
  OnShow = FormShow
  PixelsPerInch = 96
  TextHeight = 13
  object vgScene1: TvgScene
    Left = 0
    Top = 0
    Width = 651
    Height = 338
    Align = alClient
    Transparency = True
    DesignSnapGridShow = False
    DesignSnapToGrid = False
    DesignSnapToLines = True
    object Root1: TvgBackground
      Width = 651.000000000000000000
      Height = 338.000000000000000000
      HitTest = False
      object HudWindow1: TvgHudWindow
        Position.Point = '(118,117)'
        Width = 416.000000000000000000
        Height = 103.000000000000000000
        HitTest = False
        TabOrder = 0
        ShowCloseButton = False
        ShowSizeGrip = False
        Font.Style = vgFontBold
        TextAlign = vgTextAlignNear
        Text = #1057#1080#1085#1093#1088#1086#1085#1080#1079#1072#1094#1080#1103'...'
        object HudLabel1: TvgHudLabel
          Enabled = False
          Position.Point = '(22,69)'
          Width = 372.000000000000000000
          Height = 15.000000000000000000
          TabOrder = 0
          TextAlign = vgTextAlignCenter
          VertTextAlign = vgTextAlignCenter
          Text = 'Amazon S3 NMI Sync, ver. 1.2     '#169' IridiumIDE.com / Iraxis llc.'
        end
        object Label1: TvgLabel
          Position.Point = '(46,42)'
          Width = 320.000000000000000000
          Height = 23.000000000000000000
          TabOrder = 1
          Font.Size = 19.000000000000000000
          Font.Style = vgFontBold
          TextAlign = vgTextAlignCenter
          VertTextAlign = vgTextAlignCenter
          Text = #1048#1076#1077#1090' '#1087#1077#1088#1077#1076#1072#1095#1072' '#1092#1072#1081#1083#1072'...'
        end
      end
    end
  end
  object Timer1: TTimer
    OnTimer = Timer1Timer
    Left = 576
    Top = 16
  end
end
