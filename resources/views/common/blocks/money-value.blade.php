<div class="money-value">
    <span class="money-value-int">{{ number_format(explode('.', $amount??0)[0], 0, '.', ' ') }}.</span><span class="money-value-rest">{{ explode('.', $amount??0)[1]??'00' }}</span>
</div>
