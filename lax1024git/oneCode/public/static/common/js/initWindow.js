
	setTimeout(()=>{
		if(window&& window.innerWidth>960){
			let str = `
			<style>
				#iframe{
					height:670px;
					width:375px;
					margin:auto;
				}
				html,body{
					padding:0;
					margin:0;
					background-color:#f5f6fd;
					background-size:cover;
					background:url('/static/common/images/back.jpeg');
					background-position:center center;
					height:100vh;
					width:100vw;
					display: flex;
					align-items: center;
					justify-content: center;
				}
			</style>
			<iframe frameborder="0" src='${location.origin}' id="iframe"></iframe>
			`
			window.document.write(str)
		}
	},0)