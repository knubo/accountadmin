//
//  SetupView.h
//
//  Created by Knut Erik Borgen on 08.08.10.
//  Copyright 2010 __MyCompanyName__. All rights reserved.
//


#import <UIKit/UIKit.h>
#import <Foundation/Foundation.h>

@interface SetupView : UIView {
	IBOutlet UITextField *domain;
	IBOutlet UITextField *username;
	IBOutlet UITextField *password;
	IBOutlet UILabel *label;
	
	NSMutableData *responseData;
}



@property (nonatomic, retain) UITextField *domain;
@property (nonatomic, retain) UITextField *username;
@property (nonatomic, retain) UITextField *password;

- (IBAction)synchronizeDatabase:(id)sender;

@end
