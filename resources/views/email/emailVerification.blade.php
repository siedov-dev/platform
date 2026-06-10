<?php
/**
 * @var \App\MUser $user
 */
?>

@extends('email.layout')
@section('content')
    <h3 style="font-size: 16px; font-weight: 600;">{{__('mail.congratulations')}}</h3>
    <div>{{__('mail.you_have_reged')}}</div>
    <div>{{__('mail.follow_confirm_link')}}</div>
    <a href="{{route('cabinet.verifyEmail', [], true)}}?token={{ $user->email_verification_token }}&email={{ $user->email  }}" style="
            border: 1px solid #0f0253;
            padding: 12px 20px;
            border-radius: 5px;
            margin-top: 20px;
            display: inline-block;
            text-decoration: none;
            color: #0f0253;
            font-size: 14px;
        ">{{__('mail.confirm_email')}}</a>
    <div style="margin-top: 10px">{{__('mail.or_enter_code')}}<b>{{ $user->email_verification_token }}</b>{{__('mail.on_user_page')}}</div>
@endsection
