//
//  SetupView.h
//
//  Created by Knut Erik Borgen on 08.08.10.
//  Copyright 2010 Knubo Borgen. All rights reserved.
//


#import <UIKit/UIKit.h>
#import <Foundation/Foundation.h>
#import <CoreData/CoreData.h>
#import "FrittRegnskapAppDelegate.h"

@interface SetupView : UIView {
	IBOutlet UITextField *domain;
	IBOutlet UITextField *username;
	IBOutlet UITextField *password;
	IBOutlet UILabel *label;
	
	NSMutableData *responseData;
	IBOutlet FrittRegnskapAppDelegate *appDelegate;
	
	NSURLConnection *connection;
}


@property (nonatomic, retain) UITextField *domain;
@property (nonatomic, retain) UITextField *username;
@property (nonatomic, retain) UITextField *password;
@property (nonatomic, retain) NSURLConnection *connection;

- (IBAction)synchronizeDatabase:(id)sender;
- (void) loadSettings;
- (void) saveSettings;

@end
