var git = require('git-promise');

describe("exclude:media", function() {
    it("ensure media not included in commit", function(done) {
        git('ls-files ' + APP_ROOT + '/media').then(function(standardout){
            expect(standardout).toBeFalsy();
            done();
        });
    });
});
