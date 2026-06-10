@php
    /** @var \App\Models\Tournaments\Custom\CustomTournament $tournament */
@endphp

<style>
    .tournament__table{
        background-color: #0B0D31C0; 
        width: 100%;
        color: #fff
    }
    .tournament__table td{
        padding: 15px;
        vertical-align: text-top;
    }
    .margintop_30{
        margin-top: 30px;
    }
    .mob_table{
        display: block;
        overflow-x: auto;
        white-space: nowrap;
    }
    .tour_page .flex{
        display: block
    }
</style>
<div class="width-limiter tour_page">

    <div class="tournament__banner">
        <img src="{{ $tournament->getBannerUrl() }}" style="width: 100%;">
    </div>

    <div class="article__text">
        <div class="tournament__cond-title mt-2">3 ओक्स नेटवर्क टूर्नामेंट <span style="color: rgb(255, 157, 0)">Money Desert</span> – कीमत पूल <span style="color: red">50,000</span> Euro</div>
        <div class="tournament__cond-text">
            ओलाफ वाइकिंग, क्रिस्टल स्कारब्स खेलें, अधिक सोना पकड़ें!, 3 सिक्के: मिस्र, मिस्र की आग, मिस्र का सूर्य 2, सिक्का ज्वालामुखी, मिस्र का सूर्य 3, अधिक जादुई सेब, ब्लैक वुल्फ 2, हरी मिर्च 2, एज़्टेक आग, हरी मिर्च , सोना जीतो!, और पुरस्कार जीतो!
        </div>
        <div class="tournament__cond-title">Campaign period:</div>
        <div class="tournament__cond-text">
            <strong>07.12.2023 09:00 UTC</strong> to <strong>13.12.2023 23:59 UTC</strong>
        </div>
        

        <div class="tournament__cond-title">अभियान विवरण</div>
        <div class="flex ">
            <div class="tournament__cond-text">
                
                <table class="tournament__table mob_table" style="color: #fff;">
                    <tbody>
                        <tr>
                            <td>कीमत पूल</td>
                            <td>EUR 50,000</td>
                        </tr>
                        <tr>
                            <td>योग्यता</td>
                            <td>
                                योग्य होने और पुरस्कार का दावा करने का अधिकार पाने के लिए, खिलाड़ी को निम्नलिखित शर्तों को पूरा करना होगा:<br>
                                1. <strong>न्यूनतम शर्त</strong> <strong style="color: red">EUR 0.2</strong> है,<br>
                                2. न्यूनतम <strong>स्पिन की संख्या</strong> <strong style="color: red">150</strong><br> है
                                योग्यता चरण के दौरान एकत्र किए गए सभी अंक सहेजे जाते हैं और उत्तीर्ण होने के बाद खिलाड़ी के लिए गिने जाते हैं
                                योग्यता. गैर-योग्य खिलाड़ियों को भी दिखाया गया है
                            </td>
                        </tr>
                        <tr>
                            <td>टूर्नामेंट स्कोरिंग</td>
                            <td>
                                <strong style="color: red">जीत की दौड़:</strong> यदि खिलाड़ी ने <strong>EUR 1</strong> का दांव लगाया और <strong>EUR 120</strong> जीता, तो खिलाड़ी को < मिलेगा मजबूत>120 अंक</strong>.<br>
                                 <strong style="color: red">मल्टीप्लायर रेस:</strong> यदि खिलाड़ी ने <strong>EUR 2</strong> का कुल दांव लगाया और <strong>EUR 120</strong> जीता, तो खिलाड़ी को मिलेगा
                                 120(जीत)÷2(शर्त)= एक 60x गुणक = <strong>60 अंक</strong>।
                            </td>
                        </tr>
                        <tr>
                            <td>इन-गेम लीडरबोर्ड</td>
                            <td>
                                <p>टूर्नामेंट के दौरान, लीडरबोर्ड को प्रत्येक खिलाड़ी की स्पिन के साथ वास्तविक समय में अपडेट किया जाता है। बिना एक
                                    खिलाड़ी की गतिविधि, खिलाड़ी आँकड़े गेम और के बीच 10 सेकंड के सिंक पर अपडेट करने के लिए डिफ़ॉल्ट हैं
                                    प्रोमो सर्वर.
                                </p>
                                <p>
                                    जीतने वाले अंक टूर्नामेंट लीडरबोर्ड पर दिखाए और क्रमबद्ध किए जाते हैं - सर्वश्रेष्ठ खिलाड़ियों के परिणाम
                                     हमेशा ऊपर।
                                </p>
                                <p>
                                    अंतिम लीडरबोर्ड बनाते समय, हम सभी संभावित डेटा हानि को रोकने के लिए 5 मिनट की देरी रखते हैं
                                     परिदृश्य; अंतिम गणना करते समय, लीडरबोर्ड पूरा होने तक धारीदार दिखाई देगा।
                                </p>
                            </td>
                        </tr>
                    </tbody>
                </table>
                
            </div>
        </div>
        <div class="flex  margintop_30">
            <img src="/img/tournaments/money-desert/600x800_money_desert.jpg" alt="tree"style="width: 100%;">
            <div class="tournament__cond-text">
                <div class="tournament__cond-title" style="margin: 0;">पुरस्कार पूल वितरण</div>
                <ul class="tournament__list pad_left_20">
                    <li>यदि ऑटो-पेआउट सुविधा सक्षम है तो विजेताओं को पुरस्कारों का भुगतान स्वचालित रूप से किया जाता है। इसके बाद विजेता को एक विन पॉप-अप मिलता है
                        टूर्नामेंट समाप्त हो गया है और अंतिम परिणाम लीडरबोर्ड पर प्रदर्शित किए गए हैं।
                    </li>
                    <li>
                        यदि ऑटो-पेआउट सुविधा अक्षम है, तो खिलाड़ियों को एक विन पॉप-अप मिलेगा जिसमें बताया जाएगा कि पुरस्कार मैन्युअल रूप से उनके खातों में जमा किया जाएगा।
                         ऑपरेटर द्वारा 72 घंटे में
                    </li>
                    <li>
                        यदि किसी तकनीकी समस्या के कारण ऑटो-पेआउट सुविधा अनुपलब्ध है, तो ऑपरेटर विजेताओं के आधार पर खिलाड़ियों को मैन्युअल रूप से पुरस्कृत करते हैं।
                         3 ओक्स द्वारा प्रदान की गई रिपोर्ट।
                    </li>
                    <li>
                        टूर्नामेंट का कुल पुरस्कार पूल 50,000 यूरो (या खिलाड़ी की मुद्रा के बराबर) है
                    </li>
                    <li>
                        EUR में पुरस्कार वितरण तालिका:
                    </li>
                </ul>
                <table class="tournament__table margintop_30 mob_table">
                    <thead>
                        <tr>
                            <td>जगह से</td>
                            <td>रखना</td>
                            <td>पुरस्कार</td>
                            <td>जोड़</td>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>1</td>
                            <td>1</td>
                            <td><strong>€ 5,000</strong></td>
                            <td>€ 5,000</td>
                        </tr>
                        <tr>
                            <td>2</td>
                            <td>2</td>
                            <td><strong>€ 3,500</strong></td>
                            <td>€ 3,500</td>
                        </tr>
                        <tr>
                            <td>3</td>
                            <td>3</td>
                            <td><strong>€ 2,500</strong></td>
                            <td>€ 2,500</td>
                        </tr>
                        <tr>
                            <td>4</td>
                            <td>5</td>
                            <td><strong>€ 1,500</strong></td>
                            <td>€ 3,000</td>
                        </tr>
                        <tr>
                            <td>6</td>
                            <td>10</td>
                            <td><strong>€ 750</strong></td>
                            <td>€ 3,750</td>
                        </tr>
                        <tr>
                            <td>11</td>
                            <td>20</td>
                            <td><strong>€ 550</strong></td>
                            <td>€ 5,000</td>
                        </tr>
                        <tr>
                            <td>21</td>
                            <td>50</td>
                            <td><strong>€ 375</strong></td>
                            <td>€ 11,250</td>
                        </tr>
                        <tr>
                            <td>51</td>
                            <td>100</td>
                            <td><strong>€ 175</strong></td>
                            <td>€ 8,750</td>
                        </tr>
                        <tr>
                            <td>101</td>
                            <td>150</td>
                            <td><strong>€ 100</strong></td>
                            <td>€ 5,000</td>
                        </tr>
                        <tr>
                            <td>151</td>
                            <td>200</td>
                            <td><strong>€ 45</strong></td>
                            <td>€ 2,250</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
       
        <div class="tournament__cond-title">नियम और शर्तें:</div>
        
        <div class="tournament__cond-text">
            <p>
                1. टूर्नामेंट में भाग लेकर, आप यहां उल्लिखित नियमों और शर्तों और द्वारा प्रदान की गई शर्तों से सहमत हैं
                 ऑपरेटर.<br>
                 2. आपातकालीन स्थिति में, 3 ओक्स गेमिंग के पास भुगतान सहित किसी भी समय मनी डेजर्ट प्रोमो को रद्द करने का अधिकार है।
                 यदि आवश्यक समझा जाए तो जीतें।<br>
                 3. केवल योग्य खिलाड़ी ही पुरस्कार का दावा कर सकते हैं (कृपया अभियान विवरण अनुभाग देखें)।<br>
                 4. टूर्नामेंट में केवल रियल मनी राउंड ही भाग ले सकते हैं।<br>
                 5. स्पिन बनाने की कीमत के अलावा, टूर्नामेंट में प्रवेश करने के लिए कोई अतिरिक्त लागत नहीं है।<br>
                 6. प्रत्येक खिलाड़ी को लीडरबोर्ड के लिए एक व्यक्तिगत टूर्नामेंट आईडी मिलेगी।<br>
                 7. यदि दो या दो से अधिक खिलाड़ी बराबरी पर हैं, तो जो खिलाड़ी पहले स्कोर प्राप्त करेंगे वे टाई तोड़ देंगे।<br>
                 8. टूर्नामेंट के नियम कभी भी बदले जा सकते हैं।<br>
                 9. खराबी के कारण सभी भुगतान रद्द हो जाते हैं।
            </p>
        </div>

    <div class="tournament__games">
        <div class="tournament__games-head">
            <div class="tournament__games-title">{{__('tournament.tournament_games')}}:</div>
        <!--<a class="tournament__games-button" href="{{route('games', ['category' => 'tournament', 't_id' => $tournament->getId()])}}">{{__('tournament.all_games')}} ({{$tournament->getAllowedGames()->count()}})</a>-->
        </div>
        <div class="tournament__games-list">
            @foreach($tournament->getAllowedGames() as $game)
                @include('desktop.games.single-game', ['game' => $game])
            @endforeach
        </div>
    </div>

    @include('desktop.footer')

</div>
</div>
