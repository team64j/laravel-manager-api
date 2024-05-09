window.addEventListener('message', function (event) {
  if (event.data?.dark !== undefined) {
    document.documentElement.classList.toggle('dark', event.data.dark)
  }
})

document.addEventListener('DOMContentLoaded', () => {
  parent.postMessage({
    data: {
      title: document.title,
      h1: document.body.querySelector('h1').innerText
    }
  }, '*')
})

