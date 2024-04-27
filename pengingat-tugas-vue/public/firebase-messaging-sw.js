importScripts("https://www.gstatic.com/firebasejs/8.2.0/firebase-app.js")
importScripts("https://www.gstatic.com/firebasejs/8.2.0/firebase-messaging.js")

const firebaseConfig = {
  apiKey: "AIzaSyBPCaJiRBdeUk7I3AMxhlBbS74RZf60cN0",
  authDomain: "taskminder-d69cf.firebaseapp.com",
  projectId: "taskminder-d69cf",
  storageBucket: "taskminder-d69cf.appspot.com",
  messagingSenderId: "813176388039",
  appId: "1:813176388039:web:9338ee28697c9f738481e2",
  measurementId: "G-Q9TP6NHKXT",
}

firebase.initializeApp(firebaseConfig)
const messaging = firebase.messaging()

messaging.setBackgroundMessageHandler((payload) => {
  console.log("Background message received: ", payload)
  // Customize notification here
  const notificationTitle = payload.notification.title
  const notificationOptions = {
    body: payload.notification.body,
    icon: '../src/assets/taskminder_logo.png',
  }

  return self.registration.showNotification(
    notificationTitle,
    notificationOptions
  )
})