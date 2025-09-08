console.log('SPA test script loaded');

class StreamingSPA {
    constructor() {
        console.log('StreamingSPA constructor called');
        this.currentRoute = 'home';
    }
    
    init() {
        console.log('init() called');
    }
}

console.log('StreamingSPA class defined');

// Test if we can create an instance
try {
    const testSpa = new StreamingSPA();
    console.log('Test SPA instance created:', testSpa);
} catch (error) {
    console.error('Error creating test SPA instance:', error);
}
