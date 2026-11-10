<template>
    <div class="flex items-start " :class="justifyCenter ? 'justify-center' : 'gap-[86px]'">
        <div v-for="(item, index) in tabList" :key="index">
            <div 
                class="tab_item min-w-[54px] text-center" 
                :class="{ tab_active: modelValue === index }"
                @click="updateValue(index)"
            >
                {{ $t(item) }}
            </div>
        </div>
    </div>
</template>

<script setup>
const props = defineProps({
    justifyCenter: {
        type: Boolean,
        default: false
    },
    tabList: {
        type: Array,
        default: () => []
    },
    modelValue: { // 接收父组件传递的 v-model 值
        type: Number,
        default: 0
    }
});

const emit = defineEmits(['update:modelValue', "tabChange"]); // 声明事件

// 更新值的方法
const updateValue = (newValue) => {
    emit('update:modelValue', newValue); // 触发父组件更新
    emit("tabChange", newValue);
};
</script>

<style lang="less" scoped>
.tab_item {
    font-size: 14px;
    height: calc(100% + 6px);
}

.tab_active {
    position: relative;
    // background: linear-gradient(180deg, #F4C5D7 3.1005859375%, #F0A96B 34.1796875%, #EF8186 63.1103515625%, #03177D 94.7265625%);
    color: #0937AD;
    // -webkit-background-clip: text;
    // -webkit-text-fill-color: transparent;

    &::after {
        content: "";
        position: absolute;
        width: 100%;
        height: 3px;
        left: 0;
        bottom: -5px;
        background: #0937AD;
        border-radius: 1px;
    }
}
</style>
