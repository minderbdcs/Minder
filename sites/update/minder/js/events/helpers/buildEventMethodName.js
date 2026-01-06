module.exports = function(eventName) {
    function uppercaseFirsLetter(string) {
        return string ? (string.slice(0, 1).toUpperCase() + (string.slice(1) || '')) : '';
    }

    return eventName.split(/\W/).map(uppercaseFirsLetter).join('');
};