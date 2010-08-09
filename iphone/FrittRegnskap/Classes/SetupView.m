//
//  SetupView.m
//
//  Created by Knut Erik Borgen on 08.08.10.
//  Copyright 2010 __MyCompanyName__. All rights reserved.
//

#import "SetupView.h"
#import "JSON/JSON.h"

@implementation SetupView


@synthesize domain;
@synthesize username;
@synthesize password;


- (void)dealloc {
	
    [domain release];
    [username release];
    [password release];
	
    [super dealloc];
}

- (void) loadSettings {
	
	NSUserDefaults *userDefaults = [NSUserDefaults standardUserDefaults];  
	
	username.text = [userDefaults stringForKey:@"frittregnskap_username"];
	domain.text = [userDefaults stringForKey:@"frittregnskap_domain"];
	password.text = [userDefaults stringForKey:@"frittregnskap_password"];
	
}

- (void) saveSettings {
	NSUserDefaults *userDefaults = [NSUserDefaults standardUserDefaults];  
	
	[userDefaults setObject:username.text forKey:@"frittregnskap_username"];
	[userDefaults setObject:domain.text forKey:@"frittregnskap_domain"];
	[userDefaults setObject:password.text forKey:@"frittregnskap_password"];
	[userDefaults synchronize];
	
	[userDefaults release];
}

- (BOOL)textFieldShouldReturn:(UITextField *)textField {
	[textField resignFirstResponder];
	return NO;
}

- (IBAction)synchronizeDatabase:(id)sender {
	
	responseData = [[NSMutableData data] retain];
	
	NSString *uri = [NSString stringWithFormat: @"http://%@.frittregnskap.no/RegnskapServer/services/directauth/persons.php?action=changes&user=%@&password=%@",domain.text, username.text,password.text];
	
	
	NSURLRequest *request = [NSURLRequest requestWithURL:[NSURL URLWithString:uri]];
	[[NSURLConnection alloc] initWithRequest:request delegate:self];
	
}

- (void)connection:(NSURLConnection *)connection didReceiveResponse:(NSURLResponse *)response {
	[responseData setLength:0];
}

- (void)connection:(NSURLConnection *)connection didReceiveData:(NSData *)data {
	[responseData appendData:data];
}

- (void)connection:(NSURLConnection *)connection didFailWithError:(NSError *)error {
	label.text = [NSString stringWithFormat:@"Connection failed: %@", [error description]];
}

- (void)connectionDidFinishLoading:(NSURLConnection *)connection {
	[connection release];
	
	NSString *responseString = [[NSString alloc] initWithData:responseData encoding:NSUTF8StringEncoding];
	[responseData release];

	NSArray *persons = [responseString JSONValue];
	
	label.text = [NSString stringWithFormat:@"Test: %d", [persons count]];
}





@end
