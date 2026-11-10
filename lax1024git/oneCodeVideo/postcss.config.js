export default {
  plugins: {
    'postcss-pxtorem': {
      rootValue: 10, // 适用于横屏设计稿的基准
      propList: ['*','max-width'],
      selectorBlackList: [/^van-/],
      mediaQuery: false,
      minPixelValue: 1, // 需要转换的最小值，一般1px像素不转换，以上才转换
      unitPrecision: 6, // 转换成rem单位的小数点后的保留位数
    },
    tailwindcss: {},
    autoprefixer: {},
  },
}  