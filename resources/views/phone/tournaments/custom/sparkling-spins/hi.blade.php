@php
    /** @var \App\Models\Tournaments\Custom\CustomTournament $tournament */
@endphp

<style>
    .orange_bg{
        background: #d8b818!important;
        color: #000;
        font-weight: 600;
    }
    .tnt_table{
        width: 100%;
        color: #000
    }
    .tnt_table td{
        padding: 10px;
        background: #fff;
        text-align: center;
    }
    .tablett{
        width: 100%;
        color: #000;
    }
    .tablett td{
        padding: 5px;
        background: #d8b818;
    }
    .tablett2{
        width: 100%;
    }
    .tablett2 td{
        padding: 5px;
        background: #fff;
        color: #000;
        font-weight: 600;
        width: 50%;
    }
    .pdis_table{
        color: #fff;
        width: 100%;
    }
    .pdis_table td{
        padding: 3px;
        text-align: center;
        background: #fff;
        color: #000;
        font-weight: 600;
    }
    .pdis_table_title{
        padding: 5px;
        background: #d8b818;
        color: #000;
        font-weight: 600;
        margin: 2px;
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
        <div class="tournament__cond-title mt-2">3 ओक्स नेटवर्क टूर्नामेंट सीरीज <span style="color: #d8b818">Sparkling Spins</span> – कीमत पूल <span style="color: red">75,000</span> Euro</div>
        <div class="tournament__cond-text">
            गोल्ड नगेट्स, क्रिस्टल स्कारब्स, ओलाफ वाइकिंग, कॉइन वोल्केनो, सन ऑफ इजिप्ट 3 खेलें
             अधिक जादुई सेब, मिस्र का सूरज 2, काला भेड़िया 2, मिस्र की आग, हरी मिर्च 2, एज़्टेक आग
             777 सिक्के, हिट मोर गोल्ड!, मैजिक एप्पल, हरी मिर्च, 3 तीखी मिर्च, ग्रैब द गोल्ड!, टाइगर जेम्स, हिट द गोल्ड!, सन ऑफ इजिप्ट, और 75,000 यूरो जीतें!
        </div>
        
        <div class="flex">
            <div>
                <img src="/img/tournaments/sparkling-spins/344x515_sparkling_spins.jpg" alt="tree" style="width: 100%; margin-top: 30px;">
            </div>
            <div>
                <div class="tournament__cond-title">स्पार्कलिंग स्पिन्स चलते हैं:</div>
                <div class="tournament__cond-text">
                    <span style="color: #d8b818;">20.12.2023 00:00</span> UTC को <span style="color: #d8b818;">25.12.2023 23:00</span> UTC
                </div>
                
                <div class="tournament__cond-title">ग्रैंड टोटल प्राइज़ पूल:</div>
                <div class="tournament__cond-text">
                    इस अभियान के लिए ग्रैंड टोटल पुरस्कार पूल - <span style="color: red">EUR 75,000</span> (या खिलाड़ी की मुद्रा के बराबर) - निम्नलिखित संरचना के अनुसार कई पूलों से संयुक्त है:<br>
                     <span style="color: red">EUR 75,000</span> <span style="color: red">20.12.2023 00:00 UTC</span> से <span style="color: red" की अवधि के लिए ">25.12.2023 23:00 यूटीसी</span>
                    <ul>
                        प्रत्येक नियमित टूर्नामेंट के लिए <li><span style='color: red'>EUR 15,000</span> प्रदान किया जाता है, कुल मिलाकर <span style='color: red'>EUR 45,000</span> दिया जाता है। "रंग: लाल">3</span>टूर्नामेंट</li>
                         <li><span style="color: red">EUR 30,000</span> को 1 व्यक्तिगत श्रृंखला में सीरीज लीडरबोर्ड के माध्यम से सम्मानित किया जाता है</li>
                    </ul>
                </div>
                
                <div class="tournament__cond-title">टूर्नामेंट पुरस्कार पूल:</div>
                <div class="tournament__cond-text">
                    <p><strong style="color: red">EUR 15,000</strong> (या खिलाड़ी की मुद्रा के बराबर) प्रत्येक टूर्नामेंट के लिए प्रदान किया जाता है, कुल मिलाकर <strong style="color: red">EUR 45,000</ मजबूत>3 टूर्नामेंट में।</p>
                    <p>कृपया, अधिक विवरण के लिए <strong>पुरस्कार पूल वितरण</strong> अनुभाग देखें।</p>
                </div>
                
                <div class="tournament__cond-title">श्रृंखला अतिरिक्त पुरस्कार पूल:</div>
                <div class="tournament__cond-text">
                    <p><strong style="color: red">EUR 15,000</strong> 1 व्यक्तिगत श्रृंखला में सीरीज लीडरबोर्ड के माध्यम से प्रदान किया जाता है।</p>
                     <p>कृपया, अधिक विवरण के लिए <strong style="color: red">पुरस्कार पूल वितरण अनुभाग</strong> देखें। </p>
                </div>
            </div>
        </div>

        <div class="tournament__cond-title">टूर्नामेंट श्रृंखला संरचना:</div>
        <div class="tournament__cond-text">
            <p><span style="color: red">1</span> व्यक्तिगत श्रृंखला <span style="color: red">3</span> प्रत्येक नियमित टूर्नामेंट के साथ:</p>
            <table class="tnt_table">
                <thead class="">
                    <tr>
                        <td class="orange_bg" style="text-align: center;">Sparkling Spins 20.12 - 25.12</td>
                    </tr>
                </thead>
            </table>
            <table class="tnt_table mob_table">
                <tbody>
                    <tr>
                        <td class="orange_bg">TNTs</td>
                        <td>TNT1</td>
                        <td>TNT2</td>
                        <td>TNT3</td>
                        <td class="orange_bg">Series</td>
                    </tr>
                    <tr>
                        <td class="orange_bg">समय शुरू</td>
                        <td>
                            20.12.2023 00:00 UTC
                        </td>
                        <td>
                            22.12.2023 00:00 UTC
                        </td>
                        <td>
                            24.12.2023 00:00 UTC
                        </td>
                        <td class="orange_bg">20.12.2023 00:00 UTC</td>
                    </tr>
                    <tr>
                        <td class="orange_bg">समय समाप्त</td>
                        <td>21.12.2023 23:00 UTC</td>
                        <td>23.12.2023 23:00 UTC</td>
                        <td>25.12.2023 23:00 UTC</td>
                        <td class="orange_bg">25.12.2023 23:00 UTC</td>
                    </tr>
                    <tr>
                        <td class="orange_bg">स्कोरिंग</td>
                        <td>MR</td>
                        <td>WR</td>
                        <td>MR</td>
                        <td class="orange_bg"></td>
                    </tr>
                    <tr>
                        <td class="orange_bg">कीमत पूल</td>
                        <td>EUR 15,000 E</td>
                        <td>EUR 15,000 E</td>
                        <td>EUR 15,000 E</td>
                        <td class="orange_bg">EUR 30,000</td>
                    </tr>
                </tbody>
            </table>
        </div>
       

        <div class="tournament__cond-title">योग्यता:</div>
        <div class="tournament__cond-text">
            <p><strong style="color: red">EUR 15,000</strong> (या खिलाड़ी की मुद्रा के बराबर) प्रत्येक टूर्नामेंट के लिए प्रदान किया जाता है, कुल मिलाकर <strong style="color: red">EUR 45,000</ मजबूत>3 टूर्नामेंट में।</p>
            <p>कृपया, अधिक विवरण के लिए <strong>पुरस्कार पूल वितरण</strong> अनुभाग देखें।</p>
        </div>

        <div class="tournament__cond-title">टूर्नामेंट स्कोरिंग (विजेता मैकेनिक):</div>
        <div class="tournament__cond-text">
            <p>
                <strong style="color: red">जीत की दौड़:</strong> यदि खिलाड़ी ने <strong>EUR 1</strong> का दांव लगाया और <strong>EUR 120</strong> जीता, तो खिलाड़ी को < मिलेगा मजबूत>120 अंक</strong>.<br>
                 <strong style="color: red">मल्टीप्लायर रेस:</strong> यदि खिलाड़ी ने <strong>EUR 2</strong> का कुल दांव लगाया और <strong>EUR 120</strong> जीता, तो खिलाड़ी को मिलेगा
                 120(जीत)÷2(शर्त)= एक 60x गुणक = <strong>60 अंक</strong>।
            </p>
        </div>
        <div class="tournament__cond-title">श्रृंखला अतिरिक्त स्कोरिंग (विजेता मैकेनिक):</div>
        <div class="tournament__cond-text">
            <p>खिलाड़ियों को निम्नलिखित मानदंडों के आधार पर पूरे अभियान के दौरान सीरीज अतिरिक्त अंक मिल रहे हैं</p>
            <table class="tablett">
                <tr>
                    <td style="width: 50%;">प्रत्येक नियमित टीएनटी में अर्हता प्राप्त करने के लिए</td>
                    <td>200 अंक</td>
                </tr>
            </table>
            <table class="tablett">
                <tr>
                    <td style="width: 50%;">संपूर्ण नियमित टीएनटी के दौरान बेट्स में प्रत्येक EUR 100 के लिए</td>
                    <td>1 बिंदु</td>
                </tr>
            </table>
            <table class="tablett">
                <tr>
                    <td style="width: 50%;">नियमित टीएनटी में जगह के लिए</td>
                    <td>
                        <table class="tablett2 mob_table">
                            <tr>
                                <td>1st</td>
                                <td>1000 अंक</td>
                            </tr>
                            <tr>
                                <td>2nd</td>
                                <td>500 अंक</td>
                            </tr>
                            <tr>
                                <td>3rd</td>
                                <td>250 अंक</td>
                            </tr>
                            <tr>
                                <td>4th-5th</td>
                                <td>200 अंक</td>
                            </tr>
                            <tr>
                                <td>6th-10th</td>
                                <td>150 अंक</td>
                            </tr>
                            <tr>
                                <td>11th-100th</td>
                                <td>50 अंक</td>
                            </tr>
                            <tr>
                                <td>101st-150th</td>
                                <td>30 अंक</td>
                            </tr>
                        </table>
                    </td>
                </tr>
            </table>
            <img src="/img/tournaments/sparkling-spins/1200x300_sparkling_spins.jpg" alt="tree" style="width: 100%; margin-top: 30px;">
        </div>

        <div class="tournament__cond-text">
            <div class="flex">
                <div>
                    <div class="tournament__cond-title">पुरस्कार पूल वितरण:</div>
                    <p>
                        1. यदि <strong>ऑटो-पेआउट सुविधा</strong> सक्षम है तो विजेताओं को पुरस्कारों का भुगतान स्वचालित रूप से किया जाता है। इसके बाद खिलाड़ी को एक विन पॉप-अप मिलता है
                         टूर्नामेंट समाप्त हो गया है और अंतिम परिणाम लीडरबोर्ड पर प्रदर्शित किए गए हैं।<br>
                         2. यदि <strong>ऑटो-पेआउट सुविधा अक्षम है</strong>, तो खिलाड़ियों को एक विन पॉप-अप मिलता है जिसमें कहा गया है कि पुरस्कार मैन्युअल रूप से उनके खाते में जमा किया जाएगा
                         ऑपरेटर द्वारा <strong style="color: red;">72 घंटे</strong>में खाते।<br>
                         3. यदि किसी तकनीकी समस्या के कारण ऑटो-पेआउट सुविधा अनुपलब्ध है, तो ऑपरेटर विजेताओं के आधार पर खिलाड़ियों को मैन्युअल रूप से पुरस्कृत करते हैं।
                         3 ओक्स द्वारा प्रदान की गई रिपोर्ट।
                    </p>
                </div>
            </div>
           <div class="flex">
                <div style="width: 100%;">
                    <div class="pdis_table_title">EUR में नियमित टूर्नामेंट पुरस्कार पूल</div>
                    <table class="pdis_table">
                        <thead>
                            <tr>
                                <td>स्थान से</td>
                                <td>रखें</td>
                                <td>पुरस्कार</td>
                                <td>योग</td>  
                            </tr>    
                        </thead> 
                        <tbody>
                            <tr>
                                <td>1</td>    
                                <td>1</td>    
                                <td>€ 1,500</td>    
                                <td>€ 1,500</td>    
                            </tr>   
                            <tr>
                                <td>2</td>    
                                <td>2</td>    
                                <td>€ 1,250</td>    
                                <td>€ 1,250</td>    
                            </tr>   
                            <tr>
                                <td>3</td>    
                                <td>3</td>    
                                <td>€ 1,000</td>    
                                <td>€ 1,000</td>    
                            </tr>   
                            <tr>
                                <td>4</td>    
                                <td>4</td>    
                                <td>€ 750</td>    
                                <td>€ 750</td>    
                            </tr> 
                            <tr>
                                <td>5</td>    
                                <td>5</td>    
                                <td>€ 500</td>    
                                <td>€ 500</td>    
                            </tr>
                            <tr>
                                <td>6</td>    
                                <td>10</td>    
                                <td>€ 300</td>    
                                <td>€ 1,500</td>    
                            </tr>  
                            <tr>
                                <td>11</td>    
                                <td>20</td>    
                                <td>€ 200</td>    
                                <td>€ 2,000</td>    
                            </tr> 
                            <tr>
                                <td>21</td>    
                                <td>50</td>    
                                <td>€ 100</td>    
                                <td>€ 3,000</td>    
                            </tr> 
                            <tr>
                                <td>51</td>    
                                <td>100</td>    
                                <td>€ 50</td>    
                                <td>€ 2,500</td>    
                            </tr> 
                            <tr>
                                <td>101</td>    
                                <td>150</td>    
                                <td>€ 20</td>    
                                <td>€ 1,000</td>    
                            </tr> 
                            <tr>
                                <td> </td>    
                                <td> </td>    
                                <td> </td>    
                                <td>€ 15,000</td>    
                            </tr> 
                        </tbody> 
                    </table>
                </div>
                <div style="width: 100%;">
                    <div class="pdis_table_title">EUR में सीरीज लीडरबोर्ड पुरस्कार पूल</div>
                    <table class="pdis_table">
                        <thead>
                            <tr>
                                <td>स्थान से</td>
                                <td>रखें</td>
                                <td>पुरस्कार</td>
                                <td>योग</td>  
                            </tr>    
                        </thead> 
                        <tbody>
                            <tr>
                                <td>1</td>    
                                <td>1</td>    
                                <td>€ 3,000</td>    
                                <td>€ 3,000</td>    
                            </tr> 
                            <tr>
                                <td>2</td>    
                                <td>2</td>    
                                <td>€ 2,500</td>    
                                <td>€ 2,500</td>    
                            </tr> 
                            <tr>
                                <td>3</td>    
                                <td>3</td>    
                                <td>€ 2,000</td>    
                                <td>€ 2,000</td>    
                            </tr> 
                            <tr>
                                <td>4</td>    
                                <td>4</td>    
                                <td>€ 1,500</td>    
                                <td>€ 1,500</td>    
                            </tr> 
                            <tr>
                                <td>5</td>    
                                <td>5</td>    
                                <td>€ 1,000</td>    
                                <td>€ 1,000</td>    
                            </tr> 
                            <tr>
                                <td>6</td>    
                                <td>9</td>    
                                <td>€ 700</td>    
                                <td>€ 2,800</td>    
                            </tr> 
                            <tr>
                                <td>10</td>    
                                <td>17</td>    
                                <td>€ 400</td>    
                                <td>€ 3,200</td>    
                            </tr> 
                            <tr>
                                <td>18</td>    
                                <td>25</td>    
                                <td>€ 300</td>    
                                <td>€ 2,400</td>    
                            </tr> 
                            <tr>
                                <td>26</td>    
                                <td>41</td>    
                                <td>€ 200</td>    
                                <td>€ 3,200</td>    
                            </tr> 
                            <tr>
                                <td>42</td>    
                                <td>66</td>    
                                <td>€ 100</td>    
                                <td>€ 2,500</td>    
                            </tr>
                            <tr>
                                <td>67</td>    
                                <td>91</td>    
                                <td>€ 75</td>    
                                <td>€ 1,875</td>    
                            </tr>
                            <tr>
                                <td>92</td>    
                                <td>143</td>    
                                <td>€ 50</td>    
                                <td>€ 2,600</td>    
                            </tr>
                            <tr>
                                <td>144</td>    
                                <td>200</td>    
                                <td>€ 25</td>    
                                <td>€ 1,425</td>    
                            </tr>
                            <tr>
                                <td></td>    
                                <td></td>    
                                <td></td>    
                                <td>€ 30,000</td>    
                            </tr>
                        </tbody>
                    </table>
                </div>
           </div>
        </div>


       
        <div class="tournament__cond-title">नियम और शर्तें:</div>
        
        <div class="tournament__cond-text">
            <p>
                1. टूर्नामेंट में भाग लेकर, आप यहां उल्लिखित नियमों और शर्तों और द्वारा प्रदान की गई शर्तों से सहमत हैं
                 ऑपरेटर.<br>
                 2. आपातकालीन स्थिति में, 3 ओक्स गेमिंग भुगतान सहित किसी भी समय स्पार्कलिंग स्पिन्स प्रोमो को रद्द करने का अधिकार रखता है।
                 जीत के लिए, यदि आवश्यक समझा जाए।<br>
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
