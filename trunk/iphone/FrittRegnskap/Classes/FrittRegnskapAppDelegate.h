//
//  FrittRegnskapAppDelegate.h
//  FrittRegnskap
//
//  Created by Knut Erik Borgen on 08.08.10.
//  Copyright __MyCompanyName__ 2010. All rights reserved.
//

#import <UIKit/UIKit.h>

@class FrittRegnskapViewController;

@interface FrittRegnskapAppDelegate : NSObject <UIApplicationDelegate> {
    UIWindow *window;
    FrittRegnskapViewController *viewController;
}

@property (nonatomic, retain) IBOutlet UIWindow *window;
@property (nonatomic, retain) IBOutlet FrittRegnskapViewController *viewController;

@end

