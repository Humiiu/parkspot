// File generated manually based on Firebase project configuration.
// ignore_for_file: type=lint
import 'package:firebase_core/firebase_core.dart' show FirebaseOptions;
import 'package:flutter/foundation.dart'
    show defaultTargetPlatform, kIsWeb, TargetPlatform;

/// Default [FirebaseOptions] for use with your Firebase apps.
class DefaultFirebaseOptions {
  static FirebaseOptions get currentPlatform {
    if (kIsWeb) {
      return web;
    }
    switch (defaultTargetPlatform) {
      case TargetPlatform.android:
        return android;
      case TargetPlatform.iOS:
        return ios;
      default:
        throw UnsupportedError(
          'DefaultFirebaseOptions are not supported for this platform.',
        );
    }
  }

  static const FirebaseOptions web = FirebaseOptions(
    apiKey: 'AIzaSyCsXo0QYBqhSH3Ba8jzHIqrEk4_wgnpeq0',
    appId: '1:176049377073:web:47c9433c39880a8fa4aa10',
    messagingSenderId: '176049377073',
    projectId: 'parkspot-f925f',
    authDomain: 'parkspot-f925f.firebaseapp.com',
    databaseURL:
        'https://parkspot-f925f-default-rtdb.asia-southeast1.firebasedatabase.app',
    storageBucket: 'parkspot-f925f.firebasestorage.app',
    measurementId: 'G-0QMYGESZ9M',
  );

  static const FirebaseOptions android = FirebaseOptions(
    apiKey: 'AIzaSyCsXo0QYBqhSH3Ba8jzHIqrEk4_wgnpeq0',
    appId: '1:176049377073:android:f1c3e71feecde80ba4aa10',
    messagingSenderId: '176049377073',
    projectId: 'parkspot-f925f',
    databaseURL:
        'https://parkspot-f925f-default-rtdb.asia-southeast1.firebasedatabase.app',
    storageBucket: 'parkspot-f925f.firebasestorage.app',
  );

  static const FirebaseOptions ios = FirebaseOptions(
    apiKey: 'AIzaSyCsXo0QYBqhSH3Ba8jzHIqrEk4_wgnpeq0',
    appId: '1:176049377073:android:f1c3e71feecde80ba4aa10',
    messagingSenderId: '176049377073',
    projectId: 'parkspot-f925f',
    databaseURL:
        'https://parkspot-f925f-default-rtdb.asia-southeast1.firebasedatabase.app',
    storageBucket: 'parkspot-f925f.firebasestorage.app',
  );
}

